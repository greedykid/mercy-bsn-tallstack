<x-slot:title>
    Masuk ke MERCY - KAN
</x-slot>

<form 
    wire:submit="login"
    class="mx-auto w-full max-w-sm space-y-4"
>
    <div class="text-center mb-6">
        <h2 class="text-lg font-bold text-neutral-900 dark:text-white">Masuk ke Sistem MERCY</h2>
        <p class="text-xs text-neutral-500 mt-1">Sistem Informasi Manajemen Akreditasi dan Surveilen KAN</p>
    </div>

    <div class="space-y-4">
        <x-input
            id="email"
            label="Alamat Email"
            wire:model="form.email"
            type="email"
            placeholder="nama@mercy.local"
            required
            autofocus
        />

        <div>
            <div class="flex items-center justify-between mb-1">
                <label for="password" class="block text-xs font-medium text-neutral-700 dark:text-neutral-300">
                    Kata Sandi
                </label>
                <a href="{{ route('forgot-password') }}" class="text-xs text-neutral-500 hover:text-neutral-800 dark:hover:text-neutral-200" wire:navigate>
                    Lupa sandi?
                </a>
            </div>
            <x-password
                id="password"
                wire:model="form.password"
                placeholder="••••••••"
                required
            />
        </div>
    </div>

    <div class="pt-2">
        <x-button 
            type="submit"
            color="primary"
            class="w-full justify-center"
            loading
        >
            Masuk ke Sistem
        </x-button>
    </div>

    <div class="pt-3 text-center border-t border-neutral-200 dark:border-neutral-800">
        <p class="text-[11px] text-neutral-500 dark:text-neutral-400">
            Pendaftaran akun hanya dapat dilakukan oleh Administrator KAN.
        </p>
    </div>
</form>
