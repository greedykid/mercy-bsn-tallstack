<div class="mx-auto mt-16 max-w-md">
    <x-card bordered shadowless class="rounded-xl">
        <div class="text-center mb-6">
            <h2 class="text-lg font-bold text-neutral-900 dark:text-white">Konfirmasi Kata Sandi</h2>
            <p class="text-xs text-neutral-500 mt-1">Area aman. Harap masukkan kata sandi Anda sebelum melanjutkan.</p>
        </div>

        <form wire:submit="confirmPassword" class="space-y-4">
            <x-password
                label="Kata Sandi"
                wire:model.blur="password"
                required
            />
            
            <x-button 
                class="w-full justify-center mt-4" 
                type="submit"
                color="primary"
                loading
            >
                Konfirmasi
            </x-button>
        </form>
    </x-card>
</div>
