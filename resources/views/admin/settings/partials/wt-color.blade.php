{{-- One color setting of the Website theme: swatch (native color picker) +
     hex box. $source (optional) = the theme color it follows until edited by
     hand (see WebsiteTheme::autoColor), so the value moves with the theme. --}}
@php $source = $source ?? null; @endphp
<div class="wt-cf" title="{{ $hint ?? '' }}">
    <input type="color" class="wt-cf-swatch" data-wt-pair="{{ $name }}" value="{{ $value }}" aria-label="{{ $label }} picker"
           @if($source) data-wt-auto-src="{{ $source }}" data-wt-linked="{{ $linked ? '1' : '0' }}" @endif>
    <label class="wt-cf-label" for="wt_{{ $name }}">{{ $label }}@if(!empty($hint))<small>{{ $hint }}</small>@endif</label>
    <input type="text" class="wt-cf-hex" id="wt_{{ $name }}" name="{{ $name }}" value="{{ $value }}" maxlength="7" pattern="#[0-9A-Fa-f]{6}" spellcheck="false"
           @if(!empty($cssVar)) data-wt-var="{{ $cssVar }}" data-wt-kind="color" @endif>
</div>
