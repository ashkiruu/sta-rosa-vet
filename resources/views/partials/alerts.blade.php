{{-- resources/views/partials/alerts.blade.php --}}

{{-- Validation errors --}}
@if ($errors->any())
    <div class="mx-auto mt-4 mb-6 max-w-5xl rounded-2xl border border-red-200 bg-red-50 px-6 py-4 text-red-800">
        <div class="font-black uppercase text-xs tracking-widest mb-2">
            Please fix the following:
        </div>
        <ul class="list-disc pl-5 text-sm font-semibold space-y-1">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- Flash messages --}}
@php
    $flashTypes = [
        'success' => 'border-green-200 bg-green-50 text-green-800',
        'error'   => 'border-red-200 bg-red-50 text-red-800',
        'warning' => 'border-yellow-200 bg-yellow-50 text-yellow-800',
        'info'    => 'border-blue-200 bg-blue-50 text-blue-800',
    ];
@endphp

@foreach ($flashTypes as $key => $classes)
    @if (session()->has($key))
        <div class="mx-auto mt-4 mb-6 max-w-5xl rounded-2xl border px-6 py-4 {{ $classes }}">
            <div class="text-sm font-semibold">
                {{ session($key) }}
            </div>
        </div>
    @endif
@endforeach
