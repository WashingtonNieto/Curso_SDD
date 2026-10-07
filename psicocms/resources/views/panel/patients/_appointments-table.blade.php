@use('App\Models\Appointment')

<x-panel.table :caption="$caption">
    <x-slot:head>
        <th scope="col">Fecha y hora</th>
        <th scope="col">Modalidad</th>
        <th scope="col">Estado</th>
        <th scope="col">Motivo</th>
        <th scope="col">Precio</th>
        <th scope="col"><span class="sr-only">Acciones</span></th>
    </x-slot:head>

    @foreach ($appointments as $appointment)
        <tr @class(['is-cancelled' => $appointment->status === 'cancelada'])>
            <td>
                <strong>{{ ucfirst($appointment->starts_at->translatedFormat('D j M Y')) }}</strong>
                <span class="table__muted">{{ $appointment->starts_at->format('H:i') }} – {{ $appointment->ends_at->format('H:i') }}</span>
            </td>
            <td><x-panel.badge type="modality" :value="$appointment->modality" /></td>
            <td><x-panel.badge type="appointment" :value="$appointment->status" /></td>
            <td class="patient-appointments__reason">{{ $appointment->reason ? \Illuminate\Support\Str::limit($appointment->reason, 70) : '—' }}</td>
            <td>{{ $appointment->price !== null ? money($appointment->price) : '—' }}</td>
            <td>
                <div class="table__actions">
                    <a class="btn btn--icon btn--ghost" href="{{ route('panel.appointments.edit', $appointment) }}" aria-label="Editar la cita del {{ $appointment->starts_at->translatedFormat('j \d\e F \a \l\a\s H:i') }}" title="Editar cita">
                        <i class="fa-regular fa-pen-to-square" aria-hidden="true"></i>
                    </a>
                </div>
            </td>
        </tr>
    @endforeach
</x-panel.table>
