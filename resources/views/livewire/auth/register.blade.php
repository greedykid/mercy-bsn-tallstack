<x-slot:title>
    Daftar Akun Baru
</x-slot>

<form 
    wire:submit="register"
    class="mx-auto w-full max-w-md space-y-4"
>
    <div class="text-center mb-6">
        <h2 class="text-lg font-bold text-neutral-900 dark:text-white">Daftar Akun Baru</h2>
        <p class="text-xs text-neutral-500 mt-1">Sistem Informasi Akreditasi MERCY - KAN</p>
    </div>

    <div class="space-y-4">
        <x-input
            label="Nama Lengkap"
            wire:model="form.name"
            placeholder="Nama Lengkap"
            required
        />

        <x-input
            label="Alamat Email"
            wire:model="form.email"
            type="email"
            placeholder="nama@mercy.local"
            required
        />

        <x-password
            label="Kata Sandi"
            wire:model="form.password"
            placeholder="Buat kata sandi yang aman"
            required
        />

        <x-password
            label="Konfirmasi Kata Sandi"
            wire:model="form.password_confirmation"
            placeholder="Konfirmasi kata sandi"
            required
        />
    </div>

    <div class="pt-2">
        <x-button
            class="w-full justify-center"
            type="submit"
            color="primary"
            loading
        >
            Daftar Sekarang
        </x-button>
    </div>

    <div class="pt-3 text-center border-t border-neutral-200 dark:border-neutral-800">
        <a href="{{ route('login') }}" class="text-xs text-neutral-500 hover:text-neutral-800 dark:hover:text-neutral-200" wire:navigate>
            Sudah memiliki akun? <span class="font-medium underline">Masuk</span>
        </a>
    </div>
</form>
