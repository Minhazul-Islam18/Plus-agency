{{--
    Shared "evidence of competence" identity card + content blocks — the
    single source of truth for a portfolio's structured presentation (see
    ~/Documents/15-Portoflios/Recommandation sur Portofolios-*.docx: "all ICA
    references will have exactly the same architecture"). Included by:
      - the real frontend detail page (resources/views/front/portfolio-details.blade.php)
      - the admin Aperçu preview endpoint (PortfolioController@preview)
      - the admin read-only "eye" modal (PortfolioController@show)

    Expects $portfolio shaped like the Portfolio model (a real Eloquent
    instance, OR a stdClass built by the preview endpoint from unsaved form
    fields — property access only, no Eloquent-specific calls below) with:
      title, client_name, client_logo (nullable filename), sector (nullable
      object with ->name), country (nullable ISO code), service (nullable
      object with ->title), statusInfo (nullable object with ->name — the
      manageable Status module, like sector), start_date, submission_date,
      cost_of_service, partners, problematique, mission_ica,
      expertise_mobilisee, solution_approche, resultat_statut, impact, and
      the matching <field>_icon columns (nullable Font Awesome class chosen
      in the admin icon-picker, e.g. "fas fa-bullseye" — falls back to each
      block's original hardcoded icon when null).

    Also expects (passed explicitly by every call site, never read off a
    relation directly — keeps this partial usable for an unsaved preview
    that has no portfolio_id to query against):
      $galleryImages — array of image URLs (may be empty)
      $documentList  — array of ['url' => .., 'name' => .., 'size' => null|int] (may be empty)
      $clientLogoUrl — nullable resolved URL for the client logo
      $countryName   — nullable resolved country name for the ISO code

    $showIdentityRow (optional, default true) — the real frontend detail
    page has its own separate sidebar already showing Client/Sector/Country/
    Service/dates/cost/status (portfolio-details.blade.php's .dark-pd-spec),
    so it passes false here to avoid showing that same information twice on
    one page. The admin preview/eye-modal have no such sidebar of their own,
    so they keep the default (true).
--}}
@php
    $bulletize = function ($text) {
        if (empty($text)) return [];
        return array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $text))));
    };
    // Scroll-reveal (assets/front/js/dark-reveal.js + common-style.css)
    // ONLY here on request via $revealBlocks — this partial is also
    // rendered by the admin Aperçu preview and read-only eye-modal, and
    // dark-reveal.js is a front-end-only script never loaded on admin
    // pages. A .reveal-card starts at opacity:0 until that script's
    // IntersectionObserver adds .is-visible — with no observer running,
    // admin would just see permanently invisible blocks. The real
    // frontend page (portfolio-details.blade.php) is the only include
    // site passing revealBlocks => true.
    $revealBlocks = $revealBlocks ?? false;
@endphp

<div class="dark-pic-wrap">
    @if ($showIdentityRow ?? true)
    <div class="dark-pic-identity{{ $revealBlocks ? ' reveal-fade' : '' }}">
        <div class="dark-pic-identity-item">
            <span class="dark-pic-identity-icon"><i class="fas fa-building"></i></span>
            <div>
                <span class="dark-pic-identity-label">{{ __('Client') }}</span>
                <span class="dark-pic-identity-value">
                    @if (!empty($clientLogoUrl))
                        <img src="{{ $clientLogoUrl }}" alt="{{ convertUtf8($portfolio->client_name) }}" class="dark-pic-client-logo">
                    @endif
                    {{ convertUtf8($portfolio->client_name) }}
                </span>
            </div>
        </div>

        @if (!empty($portfolio->sector))
            <div class="dark-pic-identity-item">
                <span class="dark-pic-identity-icon"><i class="fas fa-industry"></i></span>
                <div>
                    <span class="dark-pic-identity-label">{{ __('Sector') }}</span>
                    <span class="dark-pic-identity-value">{{ convertUtf8($portfolio->sector->name) }}</span>
                </div>
            </div>
        @endif

        @if (!empty($portfolio->country))
            <div class="dark-pic-identity-item">
                <span class="dark-pic-identity-icon">
                    <span class="fi fi-{{ strtolower($portfolio->country) }}"></span>
                </span>
                <div>
                    <span class="dark-pic-identity-label">{{ __('Country') }}</span>
                    <span class="dark-pic-identity-value">{{ $countryName ?? $portfolio->country }}</span>
                </div>
            </div>
        @endif

        @if (!empty($portfolio->statusInfo))
            <div class="dark-pic-identity-item">
                <span class="dark-pic-identity-icon"><i class="fas fa-calendar-check"></i></span>
                <div>
                    <span class="dark-pic-identity-label">{{ __('Status') }}</span>
                    <span class="dark-pic-badge dark-pic-badge-info">
                        {{ convertUtf8($portfolio->statusInfo->name) }}
                    </span>
                </div>
            </div>
        @endif

        @if (!empty($portfolio->partnerRefs) && $portfolio->partnerRefs->isNotEmpty())
            {{-- "to be displayed only if relevant" — the doc's own words.
                 Picked from the "Our Partners" module (see the admin
                 form's multi-select) rather than free text, so real
                 partner logos show here instead of just names. --}}
            <div class="dark-pic-identity-item">
                <span class="dark-pic-identity-icon"><i class="fas fa-handshake"></i></span>
                <div>
                    <span class="dark-pic-identity-label">{{ __('Partners') }}</span>
                    <span class="dark-pic-identity-value dark-pic-partner-logos">
                        @foreach ($portfolio->partnerRefs as $partner)
                            <img src="{{ asset('assets/front/img/partners/' . $partner->image) }}"
                                alt="{{ convertUtf8($partner->name) }}" title="{{ convertUtf8($partner->name) }}"
                                class="dark-pic-partner-logo">
                        @endforeach
                    </span>
                </div>
            </div>
        @endif
    </div>
    @endif

    {{-- The 5 pipeline blocks (Problem -> Mission -> Expertise -> Solution ->
         Result) always render as cards, even with no text yet — matches the
         mockup exactly (every example shows all 5 boxes; an empty one is
         just an empty box, not a hidden one). Impact stays conditional
         below — it's not part of this row in either mockup and is
         genuinely "only if relevant" per the doc's own wording. --}}
    <div class="dark-pic-blocks">
        @php
            // Show more/less only on the real frontend page ($revealBlocks
            // is only ever true there — see the flag's own comment above)
            // — the admin Aperçu preview / read-only eye-modal load none
            // of the JS that measures/expands these, so clamping there
            // would just permanently truncate the content with no way to
            // see the rest.
            $pdClampOpen = $revealBlocks ? ' class="dark-pic-block-content"' : '';
            $pdToggle = $revealBlocks
                ? '<button type="button" class="dark-pic-toggle" hidden><span>' . e(__('Show more')) . '</span><svg viewBox="0 0 24 24" width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M9 6l6 6-6 6"/></svg></button>'
                : '';
        @endphp
        <div class="dark-pic-block{{ $revealBlocks ? ' reveal-card' : '' }}" @if ($revealBlocks) style="--d:0s" @endif>
            <div class="dark-pic-block-head">
                <span class="dark-pic-block-icon"><i class="{{ $portfolio->problematique_icon ?? 'fas fa-bullseye' }}"></i></span>
                <h4>{{ __('Problem') }}</h4>
            </div>
            <div {!! $pdClampOpen !!}><p>{{ convertUtf8($portfolio->problematique) }}</p></div>
            {!! $pdToggle !!}
        </div>

        <div class="dark-pic-block{{ $revealBlocks ? ' reveal-card' : '' }}" @if ($revealBlocks) style="--d:0.08s" @endif>
            <div class="dark-pic-block-head">
                <span class="dark-pic-block-icon"><i class="{{ $portfolio->mission_ica_icon ?? 'fas fa-file-signature' }}"></i></span>
                <h4>{{ __('Mission entrusted to ICA') }}</h4>
            </div>
            <div {!! $pdClampOpen !!}><p>{{ convertUtf8($portfolio->mission_ica) }}</p></div>
            {!! $pdToggle !!}
        </div>

        <div class="dark-pic-block{{ $revealBlocks ? ' reveal-card' : '' }}" @if ($revealBlocks) style="--d:0.16s" @endif>
            <div class="dark-pic-block-head">
                <span class="dark-pic-block-icon"><i class="{{ $portfolio->expertise_mobilisee_icon ?? 'fas fa-cogs' }}"></i></span>
                <h4>{{ __('Expertise mobilized') }}</h4>
            </div>
            <div {!! $pdClampOpen !!}>
                <ul>
                    @foreach ($bulletize($portfolio->expertise_mobilisee) as $line)
                        <li>{{ convertUtf8($line) }}</li>
                    @endforeach
                </ul>
            </div>
            {!! $pdToggle !!}
        </div>

        <div class="dark-pic-block dark-pic-block-accent{{ $revealBlocks ? ' reveal-card' : '' }}" @if ($revealBlocks) style="--d:0.24s" @endif>
            <div class="dark-pic-block-head">
                <span class="dark-pic-block-icon"><i class="{{ $portfolio->solution_approche_icon ?? 'fas fa-lightbulb' }}"></i></span>
                <h4>{{ __('Solution / Approach') }}</h4>
            </div>
            <div {!! $pdClampOpen !!}>
                <ul>
                    @foreach ($bulletize($portfolio->solution_approche) as $line)
                        <li>{{ convertUtf8($line) }}</li>
                    @endforeach
                </ul>
            </div>
            {!! $pdToggle !!}
        </div>

        <div class="dark-pic-block{{ $revealBlocks ? ' reveal-card' : '' }}" @if ($revealBlocks) style="--d:0.32s" @endif>
            <div class="dark-pic-block-head">
                <span class="dark-pic-block-icon"><i class="{{ $portfolio->resultat_statut_icon ?? 'fas fa-chart-bar' }}"></i></span>
                <h4>{{ __('Result / Status') }}</h4>
            </div>
            <div {!! $pdClampOpen !!}>
                <ul>
                    @foreach ($bulletize($portfolio->resultat_statut) as $line)
                        <li>{{ convertUtf8($line) }}</li>
                    @endforeach
                </ul>
            </div>
            {!! $pdToggle !!}
        </div>
    </div>

    @if (!empty($portfolio->impact))
        {{-- "to be displayed only if relevant" — the doc's own words, and
             neither mockup shows this as part of the 5-card row. --}}
        <div class="dark-pic-block dark-pic-impact{{ $revealBlocks ? ' reveal-card' : '' }}" @if ($revealBlocks) style="--d:0.4s" @endif>
            <div class="dark-pic-block-head">
                <span class="dark-pic-block-icon"><i class="{{ $portfolio->impact_icon ?? 'fas fa-users' }}"></i></span>
                <h4>{{ __('Impact') }}</h4>
            </div>
            <div {!! $pdClampOpen !!}><p>{{ convertUtf8($portfolio->impact) }}</p></div>
            {!! $pdToggle !!}
        </div>
    @endif

    @include('front.default.partials.dark.portfolio-documents-list', ['documentList' => $documentList ?? []])
</div>
