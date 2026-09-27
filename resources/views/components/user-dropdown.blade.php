@php
    $user = Auth::user();
@endphp

<div x-data="{ open: false }" x-on:keydown.escape.window="open = false" class="relative">
    <button 
        type="button" 
        x-on:click="open = !open" 
        x-on:click.outside="open = false" 
        class="flex items-center gap-2 p-1 min-h-[44px] min-w-[44px] justify-center sm:justify-start rounded-full hover:bg-neutral-100 dark:hover:bg-neutral-800 transition-colors focus:outline-hidden focus:ring-2 focus:ring-blue-500 cursor-pointer"
        aria-label="Menu Pengguna"
    >
        <x-avatar :text="strtoupper(substr($user->name, 0, 2))" sm color="blue" />
        <span class="hidden md:inline-block text-xs font-semibold text-neutral-700 dark:text-neutral-200 pr-1">
            {{ $user->name }}
        </span>
        <x-icon name="chevron-down" class="hidden md:block size-3.5 text-neutral-400" />
    </button>

    <div 
        x-show="open" 
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0 translate-y-1 scale-95"
        x-transition:enter-end="opacity-100 translate-y-0 scale-100"
        x-transition:leave="transition ease-in duration-100"
        x-transition:leave-start="opacity-100 translate-y-0 scale-100"
        x-transition:leave-end="opacity-0 translate-y-1 scale-95"
        class="absolute right-0 mt-2 w-64 rounded-2xl bg-white dark:bg-neutral-900 border border-neutral-200 dark:border-neutral-800 shadow-xl z-50 overflow-hidden divide-y divide-neutral-100 dark:divide-neutral-800"
        style="display: none;"
    >
        {{-- User Info Header --}}
        <div class="p-3.5 bg-neutral-50/80 dark:bg-neutral-800/50">
            <div class="flex items-center gap-2.5">
                <x-avatar :text="strtoupper(substr($user->name, 0, 2))" sm color="blue" />
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-bold text-neutral-900 dark:text-neutral-100 truncate">{{ $user->name }}</p>
                    <p class="text-[11px] text-neutral-500 dark:text-neutral-400 truncate">{{ $user->email }}</p>
                </div>
            </div>
            <div class="mt-2.5">
                <x-badge :color="$user->isAdmin() ? 'blue' : 'emerald'" sm light>
                    {{ $user->isAdmin() ? 'Administrator KAN' : 'PIC Pendamping LP' }}
                </x-badge>
            </div>
        </div>

        {{-- Menu Links --}}
        <div class="p-1.5 space-y-0.5 text-xs">
            <a 
                href="{{ route('dashboard') }}" 
                wire:navigate 
                class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800 hover:text-neutral-900 dark:hover:text-white transition-colors"
            >
                <x-icon name="squares-2x2" class="size-4 text-neutral-400" />
                <span class="font-medium">Dashboard Utama</span>
            </a>

            <a 
                href="{{ route('lpks.index') }}" 
                wire:navigate 
                class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800 hover:text-neutral-900 dark:hover:text-white transition-colors"
            >
                <x-icon name="building-office-2" class="size-4 text-neutral-400" />
                <span class="font-medium">Data Laboratorium (LP)</span>
            </a>

            <a 
                href="{{ route('deadlines.index') }}" 
                wire:navigate 
                class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800 hover:text-neutral-900 dark:hover:text-white transition-colors"
            >
                <x-icon name="bell" class="size-4 text-neutral-400" />
                <span class="font-medium">Notifikasi Deadline</span>
            </a>

            <a 
                href="{{ route('settings.account') }}" 
                wire:navigate 
                class="flex items-center gap-2.5 px-3 py-2 rounded-xl text-neutral-700 dark:text-neutral-300 hover:bg-neutral-100 dark:hover:bg-neutral-800 hover:text-neutral-900 dark:hover:text-white transition-colors"
            >
                <x-icon name="cog-6-tooth" class="size-4 text-neutral-400" />
                <span class="font-medium">Pengaturan Akun</span>
            </a>
        </div>

        {{-- Sign Out Action --}}
        <div class="p-1.5 text-xs">
            <form method="POST" action="{{ route('app.auth.logout') }}">
                @csrf
                <button 
                    type="submit" 
                    class="flex w-full items-center gap-2.5 px-3 py-2 rounded-xl text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-950/30 transition-colors font-medium cursor-pointer"
                >
                    <x-icon name="arrow-right-on-rectangle" class="size-4" />
                    <span>Keluar (Sign Out)</span>
                </button>
            </form>
        </div>
    </div>
</div>
