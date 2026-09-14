<div class="modal fade" id="editSubsectorModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Update Subsector</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
        <form id="subsectorEditForm" action="{{ route('admin.portfolio.subsector.update') }}" method="post">
          @csrf
          <input type="hidden" name="subsectorId" id="insubsectorId">

          <div class="form-group">
            <label for="">Subsector Name*</label>
            <input type="text" id="insubsectorName" class="form-control" name="name" placeholder="Ex: Beekeeping">
            <p id="eerrname" class="mt-1 mb-0 text-danger em"></p>
          </div>

          <div class="form-group">
            <label for="">Status*</label>
            <select name="status" id="insubsectorStatus" class="form-control">
              <option value="1">Active</option>
              <option value="0">Deactivated</option>
            </select>
            <p id="eerrstatus" class="mt-1 mb-0 text-danger em"></p>
          </div>

          <div class="form-group mb-0">
            <label for="">Order</label>
            <input type="number" id="insubsectorSerial" class="form-control ltr" name="serial_number" placeholder="Enter order">
            <p id="eerrserial_number" class="mt-1 mb-0 text-danger em"></p>
          </div>
        </form>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
        <button type="submit" form="subsectorEditForm" class="btn btn-primary">Update</button>
      </div>
    </div>
  </div>
</div>
