{{-- Four trust cards along the bottom edge of a hero image. Put it last inside
     <section class="hero-section has-trust">; pass $items as
     [[icon, title translation key, description translation key], ...]. --}}
<div class="hero-trust">
    @foreach($items as [$trustIcon, $trustTitle, $trustDesc])
        <div class="hero-trust-card">
            <span class="hero-trust-icon"><i class="fas {{ $trustIcon }}"></i></span>
            <span class="hero-trust-text">
                <b>{{ t($trustTitle) }}</b>
                <small>{{ t($trustDesc) }}</small>
            </span>
        </div>
    @endforeach
</div>
