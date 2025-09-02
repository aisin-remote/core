@forelse ($data as $row)
    @php
        $shortName = $row->short->name ?? null;
        $midName = $row->mid->name ?? null;
        $longName = $row->long->name ?? null;

        // status per-term (opsional, jika ingin tampil badge kecil per term)
        $shortStat = optional($row->rtcShortLatest)->status; // 0/1/null
        $midStat = optional($row->rtcMidLatest)->status;
        $longStat = optional($row->rtcLongLatest)->status;

        // helper kecil untuk badge per-term (opsional)
        $termBadge = function ($v) {
            if ($v === 1) {
                return '<span class="badge badge-info ms-1">Checked</span>';
            }
            if ($v === 0) {
                return '<span class="badge badge-warning ms-1">Submitted</span>';
            }
            return '';
        };
    @endphp

    <tr>
        <td>{{ $loop->iteration }}</td>

        {{-- Nama entity --}}
        <td class="text-center">{{ $row->name }}</td>

        {{-- Short Term --}}
        <td class="text-center">
            @if ($shortName)
                {!! e($shortName) !!} {!! $termBadge($shortStat) !!}
            @else
                <span class="text-danger">not set</span>
            @endif
        </td>

        {{-- Mid Term --}}
        <td class="text-center">
            @if ($midName)
                {!! e($midName) !!} {!! $termBadge($midStat) !!}
            @else
                <span class="text-danger">not set</span>
            @endif
        </td>

        {{-- Long Term --}}
        <td class="text-center">
            @if ($longName)
                {!! e($longName) !!} {!! $termBadge($longStat) !!}
            @else
                <span class="text-danger">not set</span>
            @endif
        </td>

        {{-- Status keseluruhan (dari backend) --}}
        <td class="text-center">
            <span class="{{ $row->overall_status_class }}">{{ $row->overall_status_label }}</span>
        </td>

        {{-- Actions --}}
        <td class="text-center">
            <a href="#" class="btn btn-sm btn-primary btn-view" data-id="{{ $row->id }}" title="Detail">
                <i class="fas fa-info-circle"></i>
            </a>

            {{-- Add Plan -> hide jika complete3 true (semua ST/MT/LT sudah ada) --}}
            @if ($row->can_add)
                <a href="#" class="btn btn-sm btn-success btn-show-modal" data-id="{{ $row->id }}"
                    data-bs-toggle="modal" data-bs-target="#addPlanModal" title="Add">
                    <i class="fas fa-plus-circle"></i>
                </a>
            @endif
        </td>
    </tr>
@empty
    <tr>
        <td colspan="7" class="text-center text-muted">No data available</td>
    </tr>
@endforelse
