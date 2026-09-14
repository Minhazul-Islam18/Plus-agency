{{-- Right panel content — re-rendered both on the page's first load and via
     AJAX every time a different sector is picked, or the subsector
     search/status filter/pagination changes (see sectors.blade.php's script). --}}
<script>
    // Reaches out to the (never-replaced) wrapper div by id, since this
    // partial's own root elements are what actually gets swapped in via
    // .html() — jQuery evaluates a <script> tag on every such insert,
    // unlike plain innerHTML, so this reliably keeps the wrapper's
    // "which sector is open" state in sync for the other handlers in
    // sectors.blade.php's own script (loadSectorPanel() reads it back).
    document.getElementById('subsectorPanelContainer').setAttribute('data-sector-id', '{{ $sector->id }}');
    // How far into the full (unpaginated) subsector list this page starts —
    // needed so a drag-reorder on page 2+ renumbers from the right starting
    // point instead of colliding with page 1's own 1..N (see
    // PortfolioSectorController::reorderSubsectors()'s own comment).
    document.getElementById('subsectorPanelContainer').setAttribute('data-page-offset', '{{ ($subsectors->currentPage() - 1) * $subsectors->perPage() }}');
</script>

<div class="card">
    <div class="card-header">
        <div class="d-flex align-items-center">
            <span class="sector-nav-icon" style="width:46px;height:46px;font-size:19px;">
                <i class="{{ $sector->icon ?: 'fas fa-building' }}"></i>
            </span>
            <div class="ml-3 flex-grow-1">
                <div class="card-title mb-0">{{ convertUtf8($sector->name) }}</div>
                <small class="text-muted">{{ $sector->subsectors()->count() }} subsectors</small>
            </div>
            <a href="#" data-toggle="modal" data-target="#createSubsectorModal" class="btn btn-success btn-sm add-subsector-btn mr-2">
                <i class="fas fa-plus"></i> Add a subsector
            </a>
            <div class="dropdown">
                <button class="btn btn-secondary btn-sm dropdown-toggle" type="button" data-toggle="dropdown">
                    <i class="fas fa-ellipsis-v"></i>
                </button>
                <div class="dropdown-menu dropdown-menu-right">
                    <a class="dropdown-item editbtn" href="#" data-toggle="modal" data-target="#editSectorModal"
                        data-id="{{ $sector->id }}" data-name="{{ $sector->name }}" data-status="{{ $sector->status }}"
                        data-serial_number="{{ $sector->serial_number }}" data-language_id="{{ $sector->language_id }}"
                        data-icon="{{ $sector->icon ?: 'fas fa-building' }}">
                        <i class="fas fa-edit"></i> Edit sector
                    </a>
                    <form action="{{ route('admin.portfolio.sector.delete') }}" method="post">
                        @csrf
                        <input type="hidden" name="sectorId" value="{{ $sector->id }}">
                        <button type="submit" class="dropdown-item text-danger deletebtn">
                            <i class="fas fa-trash"></i> Delete sector
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card-body">
        <div class="row mb-3">
            <div class="col-lg-8">
                <input type="text" id="subsectorSearchInput" class="form-control" placeholder="Search for a subsector..."
                    value="{{ request()->input('search') }}">
            </div>
            <div class="col-lg-4">
                <select id="subsectorStatusFilter" class="form-control">
                    <option value="all" {{ request()->input('status', 'all') == 'all' ? 'selected' : '' }}>All statuses</option>
                    <option value="1" {{ request()->input('status') == '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ request()->input('status') == '0' ? 'selected' : '' }}>Deactivated</option>
                </select>
            </div>
        </div>

        @if ($subsectors->isEmpty())
            <p class="text-center text-muted mb-0">No subsectors {{ request()->filled('search') || request()->input('status', 'all') !== 'all' ? 'match this filter' : 'yet' }}.</p>
        @else
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th style="width:80px;">ID</th>
                            <th>Subsector</th>
                            <th style="width:130px;">Status</th>
                            <th style="width:80px;">Order</th>
                            <th style="width:100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="subsectorSortableBody">
                        @foreach ($subsectors as $subsector)
                            <tr data-id="{{ $subsector->id }}">
                                <td>
                                    <span class="subsector-id-cell">
                                        <span class="subsector-drag-handle"><i class="fas fa-grip-vertical"></i></span>
                                        <span class="subsector-id-value">#{{ $subsector->id }}</span>
                                    </span>
                                </td>
                                <td>{{ convertUtf8($subsector->name) }}</td>
                                <td>
                                    <label class="switch mb-0">
                                        <input type="checkbox" class="subsector-status-toggle" data-id="{{ $subsector->id }}"
                                            {{ $subsector->status == 1 ? 'checked' : '' }}>
                                        <span class="slider round"></span>
                                    </label>
                                    <span class="subsector-status-label ml-1">{{ $subsector->status == 1 ? 'Active' : 'Deactivated' }}</span>
                                </td>
                                <td><span class="subsector-order-box">{{ $subsector->serial_number }}</span></td>
                                <td>
                                    <div class="subsector-actions">
                                        <a href="#" class="subsector-action-btn subsector-action-edit subsector-edit-btn"
                                            data-id="{{ $subsector->id }}" data-name="{{ $subsector->name }}"
                                            data-status="{{ $subsector->status }}" data-serial_number="{{ $subsector->serial_number }}"
                                            title="Edit"><i class="fas fa-edit"></i></a>
                                        <form action="{{ route('admin.portfolio.subsector.delete') }}" method="post" class="d-inline-block">
                                            @csrf
                                            <input type="hidden" name="subsectorId" value="{{ $subsector->id }}">
                                            <button type="submit" class="subsector-action-btn subsector-action-delete subsector-delete-btn" title="Delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <small class="text-muted">Showing {{ $subsectors->firstItem() }} to {{ $subsectors->lastItem() }} of {{ $subsectors->total() }} subsectors</small>
                {{ $subsectors->onEachSide(1)->links() }}
            </div>
        @endif
    </div>
</div>
