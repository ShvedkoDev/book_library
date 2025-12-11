<div class="resource-guide-special-block">
    @if(isset($title))
    <h3>{{ $title }}</h3>
    @endif
    @if(isset($content))
    <div class="special-block-content">
        {!! $content !!}
    </div>
    @endif
</div>
