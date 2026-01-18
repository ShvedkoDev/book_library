{{-- TCPDF-compatible cover page --}}
{{-- Gradients are drawn by PHP service, HTML just overlays content --}}

{{-- Header text over gradient (gradient drawn by service) --}}
<table cellpadding="0" cellspacing="0" border="0" style="width:100%; height:14mm;">
    <tr>
        <td style="color:#ffffff; vertical-align:middle; padding-left:12px;">
            <span style="font-size:9px; color:#e0e8f0;">National Vernacular Language Arts (VLA) curriculum</span>
        </td>
        <td style="text-align:right; vertical-align:middle; width:180px; padding-right:12px;">
            @foreach($logos as $logo)
                <img src="{{ $logo }}" height="24" style="margin-left:5px;"/>
            @endforeach
        </td>
    </tr>
</table>

{{-- Resource library banner --}}
<table cellpadding="6" cellspacing="0" border="0" style="width:100%; background-color:#c9d3e0;">
    <tr>
        <td style="vertical-align:middle; padding-left:12px;">
            <span style="font-size:14px; font-weight:bold; color:#1d496a;">Resource library</span>
        </td>
    </tr>
</table>

{{-- Generated date bar --}}
<table cellpadding="3" cellspacing="0" border="0" style="width:100%; background-color:#f4f4f4; border-bottom:1px solid #e0e0e0;">
    <tr>
        <td style="text-align:right; font-size:8px; color:#888888; padding-right:12px;">
            Document generated on {{ $timestamp }} by {{ $generatedBy }}
        </td>
    </tr>
</table>

{{-- Content wrapper with padding --}}
<table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
<tr><td style="padding:10px 14px 6px 14px;">

{{-- Title block --}}
<div style="font-size:8px; color:#888888; text-transform:uppercase; letter-spacing:1px; margin-bottom:2px;">{{ $publisherLabel }}</div>
<div style="font-size:14px; font-weight:bold; color:#1d496a; font-family:times; line-height:1.3;">{{ $book->title }}</div>
@if($subtitle)
    <div style="font-size:10px; color:#666666; font-style:italic; font-family:times; margin-top:2px;">{{ $subtitle }}</div>
@endif

<br/>

{{-- Meta grid --}}
<table cellpadding="1" cellspacing="0" border="0" style="width:90%; margin-bottom:8px;">
    @foreach($meta as $i => $item)
        @if($i % 2 === 0)
            <tr>
        @endif
        <td style="font-size:8px; width:50%;">
            <span style="color:#666666;">{{ $item['label'] }}:</span>
            <span style="color:#333333; font-weight:bold;"> {{ $item['value'] }}</span>
        </td>
        @if($i % 2 === 1 || $loop->last)
            @if($i % 2 === 0)
                <td></td>
            @endif
            </tr>
        @endif
    @endforeach
</table>

<br/>

{{-- Contributors section --}}
<table cellpadding="0" cellspacing="0" border="0" style="width:100%; border-bottom:1px solid #cccccc; margin-bottom:2px;">
    <tr><td style="font-size:10px; font-weight:bold; color:#333333; padding-bottom:2px;">Contributors</td></tr>
</table>
<table cellpadding="1" cellspacing="0" border="0" style="width:100%; margin-bottom:6px;">
    @foreach($contributors as $row)
        <tr>
            <td style="font-size:8px; color:#666666; width:90px;">{{ $row['label'] }}:</td>
            <td style="font-size:8px; color:#333333;">{{ $row['value'] }}</td>
        </tr>
    @endforeach
</table>

{{-- Edition notes section --}}
<table cellpadding="0" cellspacing="0" border="0" style="width:100%; border-bottom:1px solid #cccccc; margin-bottom:2px;">
    <tr><td style="font-size:10px; font-weight:bold; color:#333333; padding-bottom:2px;">Edition notes</td></tr>
</table>
<table cellpadding="1" cellspacing="0" border="0" style="width:100%; margin-bottom:6px;">
    @foreach($editionNotes as $row)
        <tr>
            <td style="font-size:8px; color:#666666; width:90px;">{{ $row['label'] }}:</td>
            <td style="font-size:8px; color:#333333;">{{ $row['value'] }}</td>
        </tr>
    @endforeach
</table>

{{-- Classification section --}}
<table cellpadding="0" cellspacing="0" border="0" style="width:100%; border-bottom:1px solid #cccccc; margin-bottom:2px;">
    <tr><td style="font-size:10px; font-weight:bold; color:#333333; padding-bottom:2px;">Classification</td></tr>
</table>
<table cellpadding="1" cellspacing="0" border="0" style="width:100%; margin-bottom:6px;">
    @foreach($classifications as $row)
        <tr>
            <td style="font-size:8px; color:#666666; width:90px;">{{ $row['label'] }}:</td>
            <td style="font-size:8px; color:#333333;">{{ $row['value'] }}</td>
        </tr>
    @endforeach
</table>

{{-- Description section --}}
@if($description)
<table cellpadding="0" cellspacing="0" border="0" style="width:100%; border-bottom:1px solid #cccccc; margin-bottom:2px;">
    <tr><td style="font-size:10px; font-weight:bold; color:#333333; padding-bottom:2px;">Description</td></tr>
</table>
<div style="font-size:8px; color:#333333; line-height:1.4; margin-bottom:6px;">{{ $description }}</div>
@endif

{{-- Abstract section --}}
@if($abstract)
<table cellpadding="0" cellspacing="0" border="0" style="width:100%; border-bottom:1px solid #cccccc; margin-bottom:2px;">
    <tr><td style="font-size:10px; font-weight:bold; color:#333333; padding-bottom:2px;">Abstract</td></tr>
</table>
<div style="font-size:8px; color:#333333; line-height:1.4; margin-bottom:6px;">{{ $abstract }}</div>
@endif

{{-- Notes section --}}
@if($notes)
<table cellpadding="0" cellspacing="0" border="0" style="width:100%; border-bottom:1px solid #cccccc; margin-bottom:2px;">
    <tr><td style="font-size:10px; font-weight:bold; color:#333333; padding-bottom:2px;">Notes</td></tr>
</table>
<div style="font-size:8px; color:#333333; line-height:1.4; margin-bottom:6px;">{{ $notes }}</div>
@endif

{{-- Direct download link section --}}
<table cellpadding="0" cellspacing="0" border="0" style="width:100%; border-bottom:1px solid #cccccc; margin-bottom:2px;">
    <tr><td style="font-size:10px; font-weight:bold; color:#333333; padding-bottom:2px;">Direct download link</td></tr>
</table>
<table cellpadding="4" cellspacing="0" border="0" style="margin-bottom:6px; background-color:#e8f0f5;">
    <tr>
        <td style="font-size:7px; color:#1d496a;">{{ $downloadUrl }}</td>
    </tr>
</table>

{{-- Source section --}}
<table cellpadding="0" cellspacing="0" border="0" style="width:100%; border-bottom:1px solid #cccccc; margin-bottom:2px;">
    <tr><td style="font-size:10px; font-weight:bold; color:#333333; padding-bottom:2px;">Source</td></tr>
</table>
<div style="font-size:8px; color:#333333; line-height:1.4; margin-bottom:5px;">Digital document added to the VLA resource library. If you have copyright concerns or need a hard copy, please email irei@islandresearch.org for more information.</div>

{{-- Note box --}}
<table cellpadding="5" cellspacing="0" border="0" style="width:100%; background-color:#f5f5f5; border:1px solid #dddddd;">
    <tr>
        <td style="font-size:7px; color:#666666; line-height:1.4;">
            This digital document is disseminated and preserved by the FSM National VLA Curriculum Resource Library: https://micronesian.school<br/><br/>
            It is protected by copyright law and remains the property of the rights-holders. Contact irei@islandresearch.org for inquiries.
        </td>
    </tr>
</table>

</td></tr>
</table>

{{-- Footer text over gradient (gradient drawn by service at y=264) --}}
{{-- This needs absolute positioning which TCPDF doesn't support well in HTML --}}
{{-- Footer gradient and text will be added via service --}}
