@foreach ($rtcs as $index => $rtc)
    <tr>
        <td>{{ $index + 1 }}</td>
        <td class="text-center">
            @if ($rtc->employee)
                {{ $rtc->employee->name }}
            @else
                -
            @endif
        </td>
        <td class="text-center">{{ $rtc->short_term }}</td>
        <td class="text-center">{{ $rtc->mid_term }}</td>
        <td class="text-center">{{ $rtc->long_term }}</td>
        <td class="text-center">
            <!-- Tambahkan action buttons sesuai kebutuhan -->
            <button class="btn btn-sm btn-primary">Edit</button>
        </td>
    </tr>
@endforeach
