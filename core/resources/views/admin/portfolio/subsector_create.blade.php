<div class="modal fade" id="createSubsectorModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add a Subsector</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
        <form id="subsectorCreateForm" action="{{ route('admin.portfolio.subsector.store') }}" method="post">
          @csrf
          <input type="hidden" name="sector_id" id="subsectorCreateSectorId">

          <div class="form-group">
            <label for="">Subsector Name*</label>
            <input type="text" class="form-control" name="name" placeholder="Ex: Beekeeping">
            <p id="errname" class="mt-1 mb-0 text-danger em"></p>
          </div>

          <div class="form-group">
            <label for="">Status*</label>
            <select name="status" class="form-control">
              <option value="1" selected>Active</option>
              <option value="0">Deactivated</option>
            </select>
            <p id="errstatus" class="mt-1 mb-0 text-danger em"></p>
          </div>

          <div class="form-group mb-0">
            <label for="">Order</label>
            <input type="number" class="form-control ltr" name="serial_number" placeholder="Leave blank to add at the end">
            <p id="errserial_number" class="mt-1 mb-0 text-danger em"></p>
          </div>
        </form>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" form="subsectorCreateForm" class="btn btn-success">Save</button>
      </div>
    </div>
  </div>
</div>
