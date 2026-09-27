<div class="mx-auto max-w-2xl space-y-12">
    <div>
        <h1 class="text-xl font-bold tracking-tight text-neutral-900 dark:text-neutral-100">Informasi Akun</h1>
        <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Perbarui profil publik dan alamat email akun Anda</p>
        
        <x-card bordered shadowless class="mt-6 rounded-xl">
            <form wire:submit="saveChanges" class="space-y-4">
                <x-input 
                    label="Nama Lengkap" 
                    wire:model="name" 
                    required 
                />
                
                <x-input 
                    label="Alamat Email" 
                    wire:model="email" 
                    type="email" 
                    required 
                />
                
                <div class="pt-2 flex justify-end">
                    <x-button type="submit" color="primary" loading>
                        Simpan Perubahan
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>
    
    <div>
        <h2 class="text-lg font-bold tracking-tight text-neutral-900 dark:text-neutral-100">Ubah Kata Sandi</h2>
        <p class="text-xs text-neutral-500 dark:text-neutral-400 mt-1">Perbarui kata sandi untuk mengamankan akses akun Anda</p>
        
        <x-card bordered shadowless class="mt-6 rounded-xl">
            <form wire:submit="updatePassword" class="space-y-4">
                <x-password 
                    label="Kata Sandi Saat Ini" 
                    wire:model="current_password" 
                    required 
                />
                
                <x-password 
                    label="Kata Sandi Baru" 
                    wire:model="password" 
                    required 
                />
                
                <x-password 
                    label="Konfirmasi Kata Sandi Baru" 
                    wire:model="password_confirmation" 
                    required 
                />
                
                <div class="pt-2 flex justify-end">
                    <x-button type="submit" color="primary" loading>
                        Perbarui Kata Sandi
                    </x-button>
                </div>
            </form>
        </x-card>
    </div>
</div>