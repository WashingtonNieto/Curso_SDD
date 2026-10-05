@props(['caption' => null])

<div {{ $attributes->class('table-wrap') }}>
    <table class="table">
        @if ($caption)
            <caption class="sr-only">{{ $caption }}</caption>
        @endif
        @isset($head)
            <thead>
                <tr>{{ $head }}</tr>
            </thead>
        @endisset
        <tbody>
            {{ $slot }}
        </tbody>
    </table>
</div>
