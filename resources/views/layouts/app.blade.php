<x-layouts::app.sidebar :title="$title ?? null">
    <flux:main class="app-main pb-[calc(7.5rem+env(safe-area-inset-bottom))] lg:pb-10">
        <div class="app-main-inner">
            {{ $slot }}
        </div>
    </flux:main>
</x-layouts::app.sidebar>
