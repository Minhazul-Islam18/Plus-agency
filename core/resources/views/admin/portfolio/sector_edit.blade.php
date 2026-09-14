<div class="modal fade" id="editSectorModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLongTitle">Update Portfolio Sector</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
        <form id="ajaxEditForm" class="modal-form" action="{{ route('admin.portfolio.sector.update') }}" method="post">
          @csrf
          <input type="hidden" name="sectorId" id="inid">

          <div class="form-group">
            <label for="">Language *</label>
            <select name="language_id" id="inlanguage_id" class="form-control">
                <option value="" selected disabled>Select a Language</option>
                @foreach ($langs as $lang)
                    <option value="{{ $lang->id }}">{{ $lang->name }}</option>
                @endforeach
            </select>
            <p id="eerrlanguage_id" class="mt-1 mb-0 text-danger em"></p>
            <p class="text-warning mt-2">
                <small>Changing this moves the sector to a different language's list. Any portfolio still
                    using it gets its Sector cleared (a sector under a different language no longer applies
                    to it) — you'll need to re-pick one for those.</small>
            </p>
          </div>

          <div class="form-group">
            <label for="">Sector Name*</label>
            <div class="d-flex" style="gap: 8px;">
                <div class="portfolio-icon-picker">
                    <div class="btn-group d-block">
                        <button type="button" class="btn btn-sm btn-secondary iconpicker-component" tabindex="-1"
                            title="Choose an icon"><i class="fas fa-building"></i></button>
                        <button type="button" class="icp icp-dd btn btn-sm btn-secondary dropdown-toggle"
                            data-toggle="dropdown"></button>
                        <div class="dropdown-menu"></div>
                    </div>
                    <input type="hidden" id="inicon" class="portfolio-icon-input" name="icon" value="fas fa-building">
                </div>
                <input type="text" id="inname" class="form-control" name="name" placeholder="Ex: Energy">
            </div>
            <p id="eerrname" class="mt-1 mb-0 text-danger em"></p>
          </div>

          <div class="form-group">
            <label for="">Status*</label>
            <select name="status" id="instatus" class="form-control">
              <option disabled>Select a Status</option>
              <option value="1">Active</option>
              <option value="0">Deactive</option>
            </select>
            <p id="eerrstatus" class="mt-1 mb-0 text-danger em"></p>
          </div>

          <div class="form-group">
            <label for="">Serial Number*</label>
            <input type="number" id="inserial_number" class="form-control ltr" name="serial_number" placeholder="Enter Serial Number">
            <p id="eerrserial_number" class="mt-1 mb-0 text-danger em"></p>
            <p class="text-warning mt-2">
              <small>The higher the serial number is, the later the sector will be shown.</small>
            </p>
          </div>
        </form>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">
          Close
        </button>
        <button id="updateBtn" type="button" class="btn btn-primary">
          Update
        </button>
      </div>
    </div>
  </div>
</div>
