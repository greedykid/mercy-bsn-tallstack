@if ($paginator->hasPages())
    <div class="pt-4 border-t border-neutral-200 dark:border-neutral-800 flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
        {{-- Keterangan Total Data --}}
        <div class="text-neutral-500 dark:text-neutral-400 font-medium">
            Menampilkan <span class="font-semibold text-neutral-800 dark:text-neutral-200">{{ $paginator->firstItem() ?? 0 }}</span>
            sampai <span class="font-semibold text-neutral-800 dark:text-neutral-200">{{ $paginator->lastItem() ?? 0 }}</span>
            dari <span class="font-semibold text-neutral-800 dark:text-neutral-200">{{ $paginator->total() }}</span> data laboratorium
        </div>

        {{-- Navigasi Nomor Halaman --}}
        <div class="flex items-center gap-1.5">
            {{-- Tombol Halaman Sebelumnya --}}
            @if ($paginator->onFirstPage())
                <span class="inline-flex items-center justify-center size-8 rounded-lg border border-neutral-200 dark:border-neutral-800 bg-neutral-100/60 dark:bg-neutral-900 text-neutral-300 dark:text-neutral-600 cursor-not-allowed">
                    <x-icon name="chevron-left" class="size-4" />
                </span>
            @else
                <button 
                    type="button" 
                    wire:click="previousPage" 
                    class="inline-flex items-center justify-center size-8 rounded-lg border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-700 dark:text-neutral-200 hover:bg-neutral-50 dark:hover:bg-neutral-700 hover:border-neutral-300 dark:hover:border-neutral-600 transition-colors shadow-xs"
                    title="Halaman Sebelumnya"
                >
                    <x-icon name="chevron-left" class="size-4" />
                </button>
            @endif

            {{-- Nomor-Nomor Halaman --}}
            <div class="flex items-center gap-1">
                @foreach ($elements as $element)
                    @if (is_string($element))
                        <span class="px-2 py-1 text-neutral-400 dark:text-neutral-500 font-bold">...</span>
                    @endif

                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <span class="inline-flex items-center justify-center size-8 rounded-lg bg-primary-600 text-white font-bold text-xs shadow-xs">
                                    {{ $page }}
                                </span>
                            @else
                                <button 
                                    type="button" 
                                    wire:click="gotoPage({{ $page }})" 
                                    class="inline-flex items-center justify-center size-8 rounded-lg border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-700 dark:text-neutral-300 hover:bg-neutral-50 dark:hover:bg-neutral-700 hover:text-neutral-900 dark:hover:text-white transition-colors text-xs font-medium shadow-xs"
                                >
                                    {{ $page }}
                                </button>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </div>

            {{-- Tombol Halaman Berikutnya --}}
            @if ($paginator->hasMorePages())
                <button 
                    type="button" 
                    wire:click="nextPage" 
                    class="inline-flex items-center justify-center size-8 rounded-lg border border-neutral-200 dark:border-neutral-700 bg-white dark:bg-neutral-800 text-neutral-700 dark:text-neutral-200 hover:bg-neutral-50 dark:hover:bg-neutral-700 hover:border-neutral-300 dark:hover:border-neutral-600 transition-colors shadow-xs"
                    title="Halaman Berikutnya"
                >
                    <x-icon name="chevron-right" class="size-4" />
                </button>
            @else
                <span class="inline-flex items-center justify-center size-8 rounded-lg border border-neutral-200 dark:border-neutral-800 bg-neutral-100/60 dark:bg-neutral-900 text-neutral-300 dark:text-neutral-600 cursor-not-allowed">
                    <x-icon name="chevron-right" class="size-4" />
                </span>
            @endif
        </div>
    </div>
@endif
