<div class="mx-auto mt-16 max-w-lg">
    <x-card bordered shadowless class="rounded-xl text-center space-y-4">
        <h2 class="text-lg font-bold text-neutral-900 dark:text-white">Verifikasi Alamat Email</h2>
        
        <p class="text-xs text-neutral-600 dark:text-neutral-300 leading-relaxed">
            Terima kasih telah mendaftar. Sebelum memulai, silakan verifikasi alamat email Anda dengan mengeklik tautan yang baru saja kami kirimkan ke email Anda. Jika Anda tidak menerima email tersebut, kami akan mengirimkan ulang.
        </p>

        @if (session('status') == 'verification-link-sent')
            <x-alert color="emerald" light text="Tautan verifikasi baru telah dikirimkan ke alamat email yang Anda berikan saat pendaftaran." />
        @endif

        <div class="pt-2">
            <x-button color="primary" wire:click="sendVerification" loading>
                Kirim Ulang Email Verifikasi
            </x-button>
        </div>
    </x-card>
</div>
