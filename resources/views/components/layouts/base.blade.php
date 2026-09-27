<!DOCTYPE html>
<html class="h-full" lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="tallstackui_darkTheme()" x-bind:class="{ 'dark': darkTheme }">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>MERCY {{ isset($title) ? '| ' . $title : '- Sistem Informasi Akreditasi & Surveilen Lab Penguji KAN' }}</title>
        <link rel="icon" type="image/x-icon" href="{{ asset('favicon.png') }}">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700&display=swap" rel="stylesheet" />

        <tallstackui:script />
        @livewireStyles
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <script>
            function syncMercyTheme() {
                try {
                    const theme = localStorage.getItem('dark-theme');
                    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                    const isDark = theme === 'dark' || (theme === 'system' && prefersDark);
                    if (isDark) {
                        document.documentElement.classList.add('dark');
                    } else {
                        document.documentElement.classList.remove('dark');
                    }
                } catch (e) {}
            }

            syncMercyTheme();

            document.addEventListener('livewire:navigated', syncMercyTheme);
            document.addEventListener('livewire:navigating', syncMercyTheme);
            window.addEventListener('theme', syncMercyTheme);
            window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
                if (localStorage.getItem('dark-theme') === 'system') {
                    syncMercyTheme();
                }
            });

            // Instant navigation progress bar & mobile touchstart prefetch
            let navProgressTimer = null;
            document.addEventListener('livewire:navigating', () => {
                const container = document.getElementById('mercy-nav-progress');
                const bar = document.getElementById('mercy-nav-progress-bar');
                if (!container || !bar) return;

                clearTimeout(navProgressTimer);
                container.style.opacity = '1';
                bar.style.transitionDuration = '200ms';
                bar.style.width = '35%';

                navProgressTimer = setTimeout(() => {
                    bar.style.transitionDuration = '600ms';
                    bar.style.width = '80%';
                }, 150);
            });

            document.addEventListener('livewire:navigated', () => {
                const container = document.getElementById('mercy-nav-progress');
                const bar = document.getElementById('mercy-nav-progress-bar');
                if (!container || !bar) return;

                clearTimeout(navProgressTimer);
                bar.style.transitionDuration = '100ms';
                bar.style.width = '100%';
                setTimeout(() => {
                    container.style.opacity = '0';
                    setTimeout(() => {
                        bar.style.width = '0%';
                    }, 150);
                }, 100);
            });

            // Prefetch on mobile touchstart/pointerdown so SPA navigation starts before tap finishes
            const prefetchedNavUrls = new Set();
            document.addEventListener('touchstart', (e) => {
                const link = e.target.closest('a[wire\\:navigate], a[wire\\:navigate\\.hover], aside a, nav a');
                if (link && link.href && link.origin === window.location.origin && !prefetchedNavUrls.has(link.href)) {
                    prefetchedNavUrls.add(link.href);
                    link.dispatchEvent(new MouseEvent('mouseenter', { bubbles: true, cancelable: true }));
                    if (prefetchedNavUrls.size > 50) prefetchedNavUrls.clear();
                }
            }, { passive: true });
        </script>
    </head>
    <body class="min-h-full bg-neutral-50 dark:bg-neutral-950 text-neutral-900 dark:text-neutral-50 font-sans antialiased">
        {{-- High performance navigation progress indicator --}}
        <div id="mercy-nav-progress" class="fixed top-0 left-0 right-0 h-[2.5px] z-[99999] pointer-events-none opacity-0 transition-opacity duration-150">
            <div id="mercy-nav-progress-bar" class="h-full bg-blue-600 dark:bg-blue-500 w-0 transition-all ease-out shadow-[0_0_8px_rgba(37,99,235,0.7)]" style="transition-duration: 200ms;"></div>
        </div>

        <x-toast />
        <x-dialog />

        {{ $slot }}

        @livewireScripts
    </body>
</html>
