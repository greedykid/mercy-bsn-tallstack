<div class="mx-auto mt-12 max-w-md">
    <x-card bordered shadowless class="rounded-xl">
        <div class="text-center mb-6">
            <h2 class="text-lg font-bold text-neutral-900 dark:text-white">Reset your password</h2>
            <p class="text-xs text-neutral-500 mt-1">Buat kata sandi baru untuk akun MERCY Anda</p>
        </div>

        <form wire:submit="resetPassword" class="space-y-4">
            <x-input 
                label="Alamat Email" 
                wire:model.blur="email"
                type="email"
                placeholder="nama@mercy.local"
                required 
            />

            <x-password 
                label="Kata Sandi Baru" 
                wire:model.blur="password"
                placeholder="Masukkan kata sandi baru"
                required 
            />

            <x-password 
                label="Konfirmasi Kata Sandi Baru" 
                wire:model.blur="password_confirmation"
                placeholder="Konfirmasi kata sandi baru"
                required 
            />
            
            <x-button 
                class="w-full justify-center mt-2"
                type="submit"
                color="primary"
                loading
            >
                Simpan Kata Sandi Baru
            </x-button>
        </form>
    </x-card>
</div>
