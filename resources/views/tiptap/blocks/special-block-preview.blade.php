<div style="border: 2px solid #8a9ab2; border-radius: 10px; padding: 1rem; background: #f8f9fa;">
    @if(isset($title))
    <h3 style="color: #294867; margin-top: 0;">{{ $title }}</h3>
    @endif
    @if(isset($content))
    <div>
        {!! $content !!}
    </div>
    @endif
</div>
