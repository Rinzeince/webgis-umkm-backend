<div class="fi-header flex flex-col gap-y-3 pb-3 mb-2" style="border-bottom: 1px solid rgba(156, 163, 175, 0.15);">
    <div>
        <h1 class="fi-header-heading text-2xl font-bold tracking-tight text-gray-950 dark:text-white sm:text-3xl" style="font-size: 1.875rem !important; line-height: 2.25rem !important; font-weight: 800 !important; letter-spacing: -0.025em !important; display: block !important;">
            {{ $title }}
        </h1>
        <p class="fi-header-subheading mt-1 text-sm text-gray-500 dark:text-gray-400" style="font-size: 0.875rem; margin-top: 0.25rem; display: block;">
            Evaluasi model spasial SEMMA K-Means dan pengunduhan laporan eksekutif data UMKM Kabupaten Bandung Barat.
        </p>
    </div>

    @if (!empty($actions))
        <div class="mt-2 flex flex-wrap items-center gap-3" style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.75rem; margin-top: 0.5rem;">
            @foreach ($actions as $action)
                @if ($action->isVisible())
                    {{ $action }}
                @endif
            @endforeach
        </div>
    @endif
</div>
