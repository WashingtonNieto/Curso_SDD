<x-panel.card :title="'Horario semanal · '.$label" icon="fa-regular fa-calendar-days" subtitle="Pulsa en cada hueco para marcarlo como disponible. Pulsa en un día o en una hora para marcar toda la columna o la fila." flush>
    @if ($grid === [])
        <x-panel.empty-state compact icon="fa-regular fa-clock" title="No cabe ninguna sesión" text="Con tu horario actual no cabe ninguna sesión. Amplía la hora de salida o reduce la duración." />
    @else
        <div class="week-grid" data-week-grid data-url="{{ route('panel.availability.weekly', $modality) }}" data-modality="{{ $modality }}">
            <div class="week-grid__scroll">
                <table class="week-grid__table">
                    <caption class="sr-only">Huecos semanales de {{ $label }}</caption>
                    <thead>
                        <tr>
                            <th scope="col" class="week-grid__corner"><span class="sr-only">Hora</span></th>
                            @foreach ($weekdays as $number => [$short, $long])
                                <th scope="col">
                                    <button type="button" class="week-grid__day" data-toggle-day="{{ $number }}" aria-label="Marcar o desmarcar todo el {{ mb_strtolower($long) }}">
                                        {{ $short }}
                                    </button>
                                </th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($grid as $time)
                            <tr>
                                <th scope="row">
                                    <button type="button" class="week-grid__time" data-toggle-time="{{ $time }}" aria-label="Marcar o desmarcar las {{ $time }} todos los días">{{ $time }}</button>
                                </th>
                                @foreach ($weekdays as $number => [$short, $long])
                                    @php($isMarked = in_array($time, $marked[$number] ?? [], true))
                                    <td>
                                        <button type="button" class="week-grid__cell" data-weekday="{{ $number }}" data-time="{{ $time }}"
                                            aria-pressed="{{ $isMarked ? 'true' : 'false' }}" aria-label="{{ $long }} a las {{ $time }}">
                                            <i class="fa-solid fa-check" aria-hidden="true"></i>
                                        </button>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="week-grid__footer">
                <p class="week-grid__info">
                    <span data-grid-count>0</span> huecos marcados
                    <span class="week-grid__dirty" data-grid-dirty hidden>· Tienes cambios sin guardar</span>
                </p>
                <div class="week-grid__actions">
                    <button type="button" class="btn btn--ghost btn--sm" data-grid-copy-monday>
                        <i class="btn__icon fa-regular fa-copy" aria-hidden="true"></i>
                        <span class="btn__label">Copiar lunes al resto de laborables</span>
                    </button>
                    <button type="button" class="btn btn--ghost btn--sm" data-grid-clear>
                        <i class="btn__icon fa-solid fa-eraser" aria-hidden="true"></i>
                        <span class="btn__label">Desmarcar todo</span>
                    </button>
                    <button type="button" class="btn btn--primary" data-grid-save>
                        <i class="btn__icon fa-regular fa-floppy-disk" aria-hidden="true"></i>
                        <span class="btn__label">Guardar cambios</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</x-panel.card>
