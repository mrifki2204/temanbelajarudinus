@php
    $status = session('status');
    $errors = $errors ?? null;
@endphp

{{-- Alert base pakai Tailwind utility + Alpine.js untuk dismiss (tanpa Bootstrap JS) --}}
@if ($errors && $errors->any())
    <div x-data="{ show: true }" x-show="show" x-transition
         class="flex items-start gap-2 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800"
         role="alert">
        <x-icon name="exclamation-triangle-fill" class="mt-0.5 shrink-0" />
        <div class="flex-1">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
        <button type="button" @click="show = false" aria-label="Tutup" class="shrink-0 text-red-500 hover:text-red-700">
            <x-icon name="x-lg" />
        </button>
    </div>
@endif

@if (session('success'))
    <div x-data="{ show: true }" x-show="show" x-transition
         class="flex items-start gap-2 rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-800"
         role="alert">
        <x-icon name="check-circle-fill" class="mt-0.5 shrink-0" />
        <span class="flex-1">{{ session('success') }}</span>
        <button type="button" @click="show = false" aria-label="Tutup" class="shrink-0 text-green-500 hover:text-green-700">
            <x-icon name="x-lg" />
        </button>
    </div>
@endif

@if (session('error'))
    <div x-data="{ show: true }" x-show="show" x-transition
         class="flex items-start gap-2 rounded-lg border border-red-200 bg-red-50 p-3 text-sm text-red-800"
         role="alert">
        <x-icon name="exclamation-circle-fill" class="mt-0.5 shrink-0" />
        <span class="flex-1">{{ session('error') }}</span>
        <button type="button" @click="show = false" aria-label="Tutup" class="shrink-0 text-red-500 hover:text-red-700">
            <x-icon name="x-lg" />
        </button>
    </div>
@endif

@if (session('warning'))
    <div x-data="{ show: true }" x-show="show" x-transition
         class="flex items-start gap-2 rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800"
         role="alert">
        <x-icon name="exclamation-triangle-fill" class="mt-0.5 shrink-0" />
        <span class="flex-1">{{ session('warning') }}</span>
        <button type="button" @click="show = false" aria-label="Tutup" class="shrink-0 text-amber-500 hover:text-amber-700">
            <x-icon name="x-lg" />
        </button>
    </div>
@endif

@if ($status)
    <div x-data="{ show: true }" x-show="show" x-transition
         class="flex items-start gap-2 rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-800"
         role="alert">
        <x-icon name="check-circle-fill" class="mt-0.5 shrink-0" />
        <span class="flex-1">{{ $status }}</span>
        <button type="button" @click="show = false" aria-label="Tutup" class="shrink-0 text-green-500 hover:text-green-700">
            <x-icon name="x-lg" />
        </button>
    </div>
@endif
