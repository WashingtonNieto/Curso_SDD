@props([
    'name',
    'label' => null,
    'value' => null,
    'from' => '06:00',
    'to' => '23:00',
    'step' => 15,
    'hint' => null,
    'id' => null,
    'errorKey' => null,
    'wrapperClass' => '',
])

@php
    $options = [];
    $cursor = \Carbon\Carbon::createFromFormat('H:i', $from);
    $limit = \Carbon\Carbon::createFromFormat('H:i', $to);

    while ($cursor->lte($limit)) {
        $options[$cursor->format('H:i')] = $cursor->format('H:i');
        $cursor->addMinutes($step);
    }
@endphp

<x-panel.form.select
    :name="$name"
    :label="$label"
    :options="$options"
    :value="$value"
    :hint="$hint"
    :id="$id"
    :error-key="$errorKey"
    :wrapper-class="$wrapperClass"
    {{ $attributes }}
/>
