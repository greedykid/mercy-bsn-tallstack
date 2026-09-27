<div class="flex items-center gap-2.5">
    <a data-slot="button"
       class="text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/60 border border-emerald-200 dark:border-emerald-800 relative inline-flex h-9 w-9 items-center justify-center rounded-lg text-sm font-bold shadow-xs"
       href="{{ route('dashboard') }}"
       wire:navigate>
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" class="size-5">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
            <path d="m9 12 2 2 4-4" />
        </svg>
    </a>
    <a class="flex flex-col"
       href="{{ route('dashboard') }}"
       wire:navigate>
        <div class="flex items-center gap-1.5">
            <span class="text-base font-extrabold tracking-tight text-neutral-900 dark:text-white font-sans">MERCY</span>
            <span class="text-[10px] uppercase font-bold tracking-wider px-1.5 py-0.5 rounded bg-neutral-200 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300">KAN</span>
        </div>
        <span class="text-[10px] text-neutral-500 font-medium tracking-tight">Akreditasi & Surveilen</span>
    </a>
</div>
