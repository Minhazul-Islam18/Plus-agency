{{--
    "Aperçu" preview modal shell — body is filled via AJAX from
    PortfolioController@preview, which renders the same shared identity-card
    partial the real frontend page uses. Loads the frontend's own dark theme
    CSS (scoped by its own class names, .dark-pic-*) so the preview matches
    the real published render, not an admin-styled approximation.
--}}
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flag-icons@7.2.3/css/flag-icons.min.css" integrity="sha384-aQuvIWWIbpu/mSqULLDiUveyYiPoJzPKAWjUmGJ+Elm+N/LJhzfZqsutsfw870JS" crossorigin="anonymous">
<link rel="stylesheet" href="{{ url('/') }}/assets/front/css/dark-glass-vars.php?color={{ $bs->base_color }}&color2={{ $bs->secondary_base_color }}">
<link rel="stylesheet" href="{{ asset_v('assets/front/css/dark-glass.css') }}">
@include('admin.portfolio._dark_glass_button_fix')

<div class="modal fade" id="portfolioPreviewModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content" style="background: #0b0f16;">
            <div class="modal-header" style="border-bottom: 1px solid rgba(255,255,255,0.1);">
                <h5 class="modal-title" style="color:#fff;"><i class="fas fa-eye"></i> Project Preview</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color:#fff;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="portfolioPreviewBody" style="max-height: 75vh; overflow-y: auto; padding: 24px;">
                {{-- filled via AJAX --}}
            </div>
            <div class="modal-footer" style="border-top: 1px solid rgba(255,255,255,0.1);">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
