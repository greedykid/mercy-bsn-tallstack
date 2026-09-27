<x-slot:title>
    Lupa Kata Sandi
</x-slot>

<div class="mx-auto w-full max-w-md space-y-4">
    <div class="text-center mb-6">
        <h2 class="text-lg font-bold text-neutral-900 dark:text-white">Lupa Kata Sandi</h2>
        <p class="text-xs text-neutral-500 mt-1">Masukkan alamat email akun Anda untuk menerima tautan pemulihan</p>
    </div>

    @if (session('status'))
        <x-alert color="emerald" light text="Tautan pemulihan kata sandi telah dikirimkan ke alamat email Anda." />
    @else
        <form wire:submit="sendPasswordResetLink" class="space-y-4">
            <x-input 
                label="Alamat Email" 
                wire:model.blur="email" 
                type="email" 
                placeholder="nama@mercy.local"
                required 
            />

            <x-button class="w-full justify-center" type="submit" color="primary" loading>
                Kirim Tautan Pemulihan
            </x-button>

            <div class="pt-2 text-center">
                <a class="inline-flex items-center gap-1 text-xs text-neutral-500 hover:text-neutral-800 dark:hover:text-neutral-200" href="{{ route('login') }}" wire:navigate>
                    <x-icon name="chevron-left" class="size-3.5" />
                    <span>Kembali ke halaman masuk</span>
                </a>
            </div>
        </form>
    @endif
</div>
