<?php

declare(strict_types=1);

namespace App\Services\Importers;

use App\DTOs\LpkData;
use App\Enums\KanSchema;
use App\Enums\LpkStatus;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Shuchkin\SimpleXLSX;
use Throwable;

class CsvLpkImporter
{
    public function normalizeHeader(string $header): string
    {
        $h = strtolower(trim($header, " \t\n\r\0\x0B\"'"));
        $h = (string) preg_replace('/[^a-z0-9]+/', '_', $h);

        return trim($h, '_');
    }

    public function cleanDate(?string $val): ?string
    {
        if (blank($val)) {
            return null;
        }

        $val = trim((string) $val);

        // Check if numeric Excel timestamp (e.g. 45306)
        if (is_numeric($val) && (float) $val > 25569 && (float) $val < 100000) {
            try {
                return \Carbon\Carbon::create(1899, 12, 30)->addDays((int) floor((float) $val))->toDateString();
            } catch (Throwable) {}
        }

        // Check YYYY-MM-DD prefix (handles YYYY-MM-DD and YYYY-MM-DD HH:MM:SS)
        if (preg_match('/^(\d{4}-\d{2}-\d{2})/', $val, $m)) {
            return $m[1];
        }

        // Check DD/MM/YYYY or DD-MM-YYYY
        if (preg_match('/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})/', $val, $m)) {
            return sprintf('%04d-%02d-%02d', (int) $m[3], (int) $m[2], (int) $m[1]);
        }

        try {
            return \Carbon\Carbon::parse($val)->toDateString();
        } catch (Throwable) {
            return $val;
        }
    }

    /**
     * Parse konten teks CSV menjadi kumpulan LpkData
     *
     * @return Collection<int, LpkData>
     */
    public function parse(string $csvContent): Collection
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($csvContent));
        if (empty($lines)) {
            return collect();
        }

        $headerLine = array_shift($lines);
        $delimiter = str_contains($headerLine, ';') ? ';' : ',';
        $headers = array_map(fn ($h) => $this->normalizeHeader((string) $h), str_getcsv($headerLine, $delimiter, '"', '\\'));

        $rows = [];
        foreach ($lines as $line) {
            if (empty(trim($line))) {
                continue;
            }
            $rows[] = str_getcsv($line, $delimiter, '"', '\\');
        }

        return $this->parseRows($headers, $rows);
    }

    /**
     * Parse file Excel (.xlsx) menjadi kumpulan LpkData
     *
     * @return Collection<int, LpkData>
     */
    public function parseExcel(string $filePath): Collection
    {
        try {
            $xlsx = SimpleXLSX::parse($filePath);
            if (! $xlsx) {
                // Fallback: cek apakah berkas merupakan CSV/teks polos yang dinamai .xlsx
                $content = @file_get_contents($filePath);
                if ($content && ! str_contains($content, "\0")) {
                    return $this->parse($content);
                }

                \Illuminate\Support\Facades\Log::warning('SimpleXLSX parse error: ' . SimpleXLSX::parseError() . ' on ' . $filePath);
                return collect();
            }

            // Cari lembar kerja (sheet) pertama yang memiliki data baris
            $rows = [];
            foreach ($xlsx->sheetNames() as $sheetIndex => $sheetName) {
                $sheetRows = $xlsx->rows($sheetIndex);
                if (! empty($sheetRows) && count($sheetRows) > 0) {
                    $rows = $sheetRows;
                    break;
                }
            }

            if (empty($rows)) {
                $rows = $xlsx->rows();
            }

            if (empty($rows)) {
                return collect();
            }

            // Deteksi baris header aktual (mencari baris yang memuat istilah umum header LP)
            $headerIndex = 0;
            foreach ($rows as $rIdx => $r) {
                if (empty(array_filter($r, fn ($cell) => ! empty(trim((string) $cell))))) {
                    continue;
                }
                $normalized = array_map(fn ($c) => $this->normalizeHeader((string) $c), $r);
                $score = 0;
                foreach ($normalized as $cell) {
                    if (str_contains($cell, 'nama') || str_contains($cell, 'lpk') || str_contains($cell, 'reg') || str_contains($cell, 'akreditasi') || str_contains($cell, 'alamat') || str_contains($cell, 'asesmen') || str_contains($cell, 'lingkup')) {
                        $score++;
                    }
                }
                if ($score >= 2) {
                    $headerIndex = $rIdx;
                    break;
                }
            }

            $headerRow = $rows[$headerIndex];
            $dataRows = array_slice($rows, $headerIndex + 1);
            $headers = array_map(fn ($h) => $this->normalizeHeader((string) $h), $headerRow);

            return $this->parseRows($headers, $dataRows);
        } catch (Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Exception in parseExcel: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Ambil dan parse spreadsheet dari link Google Sheets publik
     *
     * @return Collection<int, LpkData>
     */
    public function parseGoogleSheets(string $url): Collection
    {
        $trimmedUrl = trim($url);

        // Jika URL sudah berbentuk export CSV
        $csvUrl = $trimmedUrl;

        if (preg_match('/spreadsheets\/d\/([a-zA-Z0-9-_]+)/', $trimmedUrl, $matches)) {
            $spreadsheetId = $matches[1];
            $gid = '0';
            if (preg_match('/[?&#]gid=([0-9]+)/', $trimmedUrl, $gidMatches)) {
                $gid = $gidMatches[1];
            }
            $csvUrl = "https://docs.google.com/spreadsheets/d/{$spreadsheetId}/export?format=csv&gid={$gid}";
        }

        try {
            $response = Http::timeout(20)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (compatible; MERCY-BSN/1.0)',
                ])
                ->get($csvUrl);

            if ($response->successful()) {
                return $this->parse($response->body());
            }
        } catch (Throwable) {
            return collect();
        }

        return collect();
    }

    /**
     * Map baris data ke kumpulan objek LpkData
     *
     * @param  array<int, string>  $headers
     * @param  array<int, array<int, mixed>>  $rows
     * @return Collection<int, LpkData>
     */
    public function parseRows(array $headers, array $rows): Collection
    {
        $results = collect();

        foreach ($rows as $row) {
            if (empty($row) || count($row) < 2) {
                continue;
            }

            $mapped = [];
            foreach ($headers as $index => $header) {
                $mapped[$header] = isset($row[$index]) ? trim((string) $row[$index]) : null;
            }

            $noRegLpk = $mapped['no_reg_lpk']
                ?? $mapped['no_reg']
                ?? $mapped['nomor_registrasi']
                ?? $mapped['no_registrasi']
                ?? $mapped['no_reg_lp']
                ?? $mapped['nomor_registrasi_lpk']
                ?? $mapped['no_registrasi_lpk']
                ?? $mapped['noreg']
                ?? $mapped['noreg_lpk']
                ?? $mapped['registrasi']
                ?? '';

            $noAkreditasi = $mapped['no_akreditasi']
                ?? $mapped['nomor_akreditasi']
                ?? $mapped['no_akreditasi_kan']
                ?? $mapped['nomor_akreditasi_kan']
                ?? $mapped['no_sertifikat']
                ?? $mapped['nomor_sertifikat']
                ?? $mapped['kode_akreditasi']
                ?? $mapped['no_sk']
                ?? $mapped['nomor_sk']
                ?? $mapped['registration_number']
                ?? '';

            if (empty($noRegLpk) && ! empty($noAkreditasi)) {
                $noRegLpk = $noAkreditasi;
            }
            if (empty($noAkreditasi) && ! empty($noRegLpk)) {
                $noAkreditasi = $noRegLpk;
            }

            $name = $mapped['nama_lpk']
                ?? $mapped['nama_laboratorium']
                ?? $mapped['nama_lp']
                ?? $mapped['nama_lab']
                ?? $mapped['nama']
                ?? $mapped['nama_lpk_laboratorium']
                ?? $mapped['nama_lembaga']
                ?? $mapped['name']
                ?? $mapped['laboratorium']
                ?? $mapped['lembaga']
                ?? '';

            if (empty($noRegLpk) || empty($name)) {
                continue;
            }

            $schemaStr = strtoupper((string) ($mapped['jenis_akreditasi'] ?? $mapped['skema'] ?? $mapped['skema_akreditasi'] ?? $mapped['kan_schema'] ?? $mapped['schema'] ?? 'LP'));
            $schema = match (true) {
                str_contains($schemaStr, 'LP'),
                str_contains($schemaStr, 'PENGUJI'),
                str_contains($schemaStr, 'TESTING'),
                str_contains($schemaStr, '17025') => KanSchema::LP,
                default => KanSchema::tryFrom($schemaStr) ?? KanSchema::LP,
            };

            $statusStr = strtoupper((string) ($mapped['status'] ?? 'ACTIVE'));
            $status = LpkStatus::tryFrom($statusStr) ?? LpkStatus::ACTIVE;

            $timAsesmen = $mapped['tim_asesmen']
                ?? $mapped['tim_asesor']
                ?? $mapped['asesor_kepala']
                ?? $mapped['lead_assessor']
                ?? $mapped['ketua_asesor']
                ?? $mapped['asesor']
                ?? null;

            $address = $mapped['alamat']
                ?? $mapped['alamat_laboratorium']
                ?? $mapped['alamat_lengkap']
                ?? $mapped['address']
                ?? $mapped['lokasi']
                ?? null;

            $phone = $mapped['telepon']
                ?? $mapped['telepon_fax']
                ?? $mapped['telp_fax']
                ?? $mapped['phone']
                ?? $mapped['no_telp']
                ?? $mapped['telp']
                ?? null;
            if ($phone === null) {
                foreach ($mapped as $k => $v) {
                    if (str_contains($k, 'telepon') || str_contains($k, 'telp') || str_contains($k, 'phone')) {
                        $phone = $v;
                        break;
                    }
                }
            }

            $driveUrl = $mapped['drive_url']
                ?? $mapped['link_drive']
                ?? $mapped['link']
                ?? $mapped['url']
                ?? $mapped['tautan']
                ?? null;
            if ($driveUrl === null) {
                foreach ($mapped as $k => $v) {
                    if (str_contains($k, 'drive') || (str_contains($k, 'link') && ! str_contains($k, 'lingkup'))) {
                        $driveUrl = $v;
                        break;
                    }
                }
            }

            $expVal = $mapped['tgl_kedaluwarsa']
                ?? $mapped['tanggal_kedaluwarsa']
                ?? $mapped['tgl_kadaluarsa']
                ?? $mapped['tanggal_kadaluarsa']
                ?? $mapped['expired_at']
                ?? $mapped['masa_berlaku']
                ?? $mapped['masa_berlaku_akreditasi_expired']
                ?? $mapped['masa_berlaku_akreditasi']
                ?? $mapped['expired']
                ?? null;
            if ($expVal === null) {
                foreach ($mapped as $k => $v) {
                    if (str_contains($k, 'kadaluarsa') || str_contains($k, 'kedaluwarsa') || str_contains($k, 'expired')) {
                        $expVal = $v;
                        break;
                    }
                }
            }

            $certDate = $this->cleanDate($mapped['tgl_sertifikat'] ?? $mapped['tanggal_sertifikat'] ?? $mapped['certificate_date'] ?? $mapped['tgl_akreditasi'] ?? $mapped['tgl_terbit'] ?? null);
            $expDate = $this->cleanDate($expVal);
            $assDate = $this->cleanDate($mapped['tgl_asesmen'] ?? $mapped['tanggal_asesmen'] ?? $mapped['tgl_kunjungan'] ?? $mapped['tanggal_kunjungan'] ?? $mapped['assessment_date'] ?? null);
            $assEndDate = $this->cleanDate($mapped['tgl_selesai_asesmen'] ?? $mapped['tanggal_selesai_asesmen'] ?? $mapped['tgl_selesai'] ?? $mapped['assessment_end_date'] ?? null);
            $tpDueDate = $this->cleanDate($mapped['batas_tp'] ?? $mapped['batas_waktu_tp'] ?? $mapped['tp_due_date'] ?? $mapped['jatuh_tempo_tp'] ?? null);

            $results->push(new LpkData(
                no_reg: $noRegLpk,
                name: $name,
                kan_schema: $schema,
                registration_number: $noAkreditasi,
                scope: $mapped['lingkup'] ?? $mapped['scope'] ?? $mapped['ruang_lingkup'] ?? null,
                address: $address,
                city: $mapped['kota'] ?? $mapped['city'] ?? null,
                province: $mapped['provinsi'] ?? $mapped['province'] ?? null,
                email: $mapped['email'] ?? null,
                phone: $phone,
                contact_person: $mapped['kontak'] ?? $mapped['contact_person'] ?? $mapped['narahubung'] ?? $mapped['personel'] ?? null,
                certificate_date: $certDate,
                expired_at: $expDate,
                notes: $mapped['catatan'] ?? $mapped['notes'] ?? $mapped['keterangan'] ?? null,
                drive_url: $driveUrl,
                status: $status,
                assessment_title: $mapped['judul_asesmen'] ?? $mapped['assessment_title'] ?? $mapped['agenda'] ?? null,
                assessment_type: $mapped['jenis_asesmen'] ?? $mapped['tipe_asesmen'] ?? $mapped['assessment_type'] ?? null,
                assessment_date: $assDate,
                assessment_end_date: $assEndDate,
                lead_assessor: $timAsesmen,
                assessor_team: $mapped['tim_asesmen'] ?? $mapped['tim_asesor'] ?? $mapped['assessor_team'] ?? $timAsesmen,
                assessment_status: $mapped['status_asesmen'] ?? $mapped['assessment_status'] ?? null,
                tp_findings_total: isset($mapped['temuan_total']) && $mapped['temuan_total'] !== '' ? (int) $mapped['temuan_total'] : (isset($mapped['total_temuan']) && $mapped['total_temuan'] !== '' ? (int) $mapped['total_temuan'] : (isset($mapped['tp_findings_total']) && $mapped['tp_findings_total'] !== '' ? (int) $mapped['tp_findings_total'] : null)),
                tp_findings_closed: isset($mapped['temuan_selesai']) && $mapped['temuan_selesai'] !== '' ? (int) $mapped['temuan_selesai'] : (isset($mapped['temuan_tertutup']) && $mapped['temuan_tertutup'] !== '' ? (int) $mapped['temuan_tertutup'] : (isset($mapped['tp_findings_closed']) && $mapped['tp_findings_closed'] !== '' ? (int) $mapped['tp_findings_closed'] : null)),
                tp_due_date: $tpDueDate,
            ));
        }

        return $results;
    }
}
