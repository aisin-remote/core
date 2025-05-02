@if ($data->count())
    @foreach ($data as $i => $item)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td class="text-center">{{ $item->name }}</td>
            <td class="text-center">
                <button class="btn btn-sm btn-info btn-view" data-id="{{ $item->id }}" title="View">
                    <i class="fas fa-eye"></i> View
                </button>
                <a href="#" class="btn btn-sm btn-success btn-show-modal" data-bs-toggle="modal"
                    data-bs-target="#addPlanModal" data-id="{{ $item->id }}">
                    <i class="fas fa-plus-circle"></i> Add
                </a>
                <a href="#" class="btn btn-sm btn-warning" title="Export">
                    <i class="fas fa-upload"></i> Export
                </a>
            </td>
        </tr>
    @endforeach
@else
    <tr>
        <td colspan="3" class="text-center">No data available</td>
    </tr>
@endif
