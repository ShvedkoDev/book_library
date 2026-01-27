{{-- TCPDF-compatible cover page --}}
{{-- Gradients are drawn by PHP service, HTML just overlays content --}}
{{-- All margin/padding replaced with TCPDF-compatible spacing techniques --}}

{{-- Header spacer (gradient drawn by service) --}}
<table cellpadding="0" cellspacing="0" border="0" style="width:1000px; height:30px;">
    <tr>
        <td>&nbsp;</td>
    </tr>
</table>
<br>
{{-- Resource library banner (background drawn by service as full-width layer) --}}
<table cellpadding="0" cellspacing="0" border="0" style="width:1000px; height:100px;">
    <tr><td style="height: 1px"></td></tr>
    <tr>
        {{-- Left side with text (using table cellpadding for spacing instead of CSS padding) --}}
        <td style="color:#ffffff; width:435px">
            <table cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td style="width:48px;"></td>
                    <td><span style="font-size:9px; color:#888888;">National Vernacular Language Arts (VLA) curriculum</span><br><span style="font-size:20px; color:#1d496a;"><strong>Resource library</strong></span></td>
                </tr>
            </table>
        </td>
        {{-- Right side with logos (using nested table for spacing) --}}
        <td style="width:170px;">
            <table cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td style="">
                        <br>
                        @foreach($logos as $logo)
                            <img src="{{ $logo }}" style="height: 35px"/>
                        @endforeach
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
<br>
<br>
{{-- Generated date bar --}}
<table cellpadding="3" cellspacing="0" style="width:1000px; background-color:#f4f4f4;">
    <tr>
        <td style="text-align:left; font-size:8px; color:#888888;">
            <table cellpadding="0" cellspacing="0" border="0" style="width:1000px;">
                <tr>
                    <td style="width:48px;"></td>
                    <td style="text-align:left;">Document generated on {{ $timestamp }} by {{ $generatedBy }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>
{{-- Border simulation using colored table row --}}
<table cellpadding="0" cellspacing="0" border="0" style="width:1000px; height: 2px; background-color:#1d496a;">
    <tr>
        <td>&nbsp;</td>
    </tr>
</table>
<table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
    <tr>
        <td style="width:48px;"></td>
        <td>
            <table cellpadding="0" cellspacing="0" border="0" style="width:600px;">
                <tr>
                    <td colspan="2" style="font-size:12px; color:#888888; text-transform:uppercase; letter-spacing:1px;"><strong>{{ $publisherLabel }}</strong></td>
                </tr>
                <tr>
                    <td colspan="2" style="height:2px;"></td>
                </tr>
                <tr>
                    <td colspan="2" style="font-size:20px; color:#1d496a; line-height:1.3;"><strong>{{ $book->title }}</strong></td>
                </tr>
                @if($subtitle)
                    <tr>
                        <td colspan="2" style="height:2px;"></td>
                    </tr>
                    <tr>
                        <td style="font-size:14px;">{{ $subtitle }}</td>
                    </tr>
                @endif
                @if($translated_title)
                    <tr>
                        <td colspan="2" style="height:2px;"></td>
                    </tr>
                    <tr>
                        <td style="font-size:14px; color:#888888; width: 120px">Translated title:</td>
                        <td style="font-size:14px;">{{ $translated_title }}</td>
                    </tr>
                @endif
            </table><br><br><table cellpadding="0" cellspacing="0" border="0" style="width:600px;">
                <tr>
                    <td  style="width: 120px">
                        <table cellspacing="0" border="0">
                            <tr><td><span style="font-size:10px; color:#333333;"><strong>{{ $metaFirst['label'] }}:</strong></span></td></tr>
                            <tr><td><span style="color:#333333; font-size: 12px"><strong>{{ $metaFirst['value'] }}</strong></span></td></tr>
                        </table>
                    </td>
                    <td  style="width: 500px">
                        <table cellspacing="0" border="0">
                            <tr><td><span style="font-size:10px; color:#333333;"><strong>{{ $metaSecond['label'] }}:</strong></span></td></tr>
                            <tr><td><span style="color:#333333; font-size: 12px"><strong>{{ $metaSecond['value'] }}</strong></span></td></tr>
                        </table>
                    </td>
                </tr>
            </table><br><br><table cellpadding="0" cellspacing="0" border="0" style="width:600px;">
                <tr>
                    <td  style="width: 120px">
                        <table cellspacing="0" border="0">
                            <tr><td><span style="font-size:10px; color:#333333;"><strong>{{ $metaThird['label'] }}:</strong></span></td></tr>
                            <tr><td><span style="color:#333333; font-size: 12px"><strong>{{ $metaThird['value'] }}</strong></span></td></tr>
                        </table>
                    </td>
                    <td  style="width: 500px">
                        <table cellspacing="0" border="0">
                            <tr><td><span style="font-size:10px; color:#333333;"><strong>{{ $metaForth['label'] }}:</strong></span></td></tr>
                            <tr><td><span style="color:#333333; font-size: 12px"><strong>{{ $metaForth['value'] }}</strong></span></td></tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td colspan="2" style="height:10px;"></td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td style="width:48px;"></td>
        <td>
            <table cellpadding="0" cellspacing="0" border="0" style="width:600px;">
                <tr><td style="font-size:10px; color:#333333;width:120px"><strong>Contributors</strong></td></tr>
            </table>
            <table cellpadding="1" cellspacing="0" border="0" style="width:500px;">
                @foreach($contributors as $row)<tr><td style="font-size:8px; color:#666666; width:120px;">{{ $row['label'] }}:</td><td style="font-size:8px; color:#333333;">{{ $row['value'] }}</td></tr>@endforeach
                <tr><td colspan="3" style="height:10px;"></td></tr>
            </table>
        </td>
    </tr>
    <tr>
        <td style="width:48px;"></td>
        <td>
            <table cellpadding="0" cellspacing="0" border="0" style="width:600px;">
                <tr><td style="font-size:10px; color:#333333;width:120px"><strong>Edition notes</strong></td></tr>
            </table>
            <table cellpadding="1" cellspacing="0" border="0" style="width:500px;">
                @foreach($editionNotes as $row)
                    <tr>
                        <td style="font-size:8px; color:#666666; width:120px;">{{ $row['label'] }}:</td>
                        <td style="font-size:8px; color:#333333;">{{ $row['value'] }}</td>
                    </tr>
                @endforeach
                <tr><td colspan="3" style="height:10px;"></td></tr>
            </table>
        </td>
    </tr>
    <tr>
        <td style="width:48px;"></td>
        <td>
            <table cellpadding="0" cellspacing="0" border="0" style="width:600px;">
                <tr><td style="font-size:10px; color:#333333;width:120px"><strong>Classification</strong></td></tr>
            </table>
            <table cellpadding="1" cellspacing="0" border="0" style="width:500px;">
                @foreach($classifications as $row)
                    <tr>
                        <td style="font-size:8px; color:#666666; width:120px;">{{ $row['label'] }}:</td>
                        <td style="font-size:8px; color:#333333;">{{ $row['value'] }}</td>
                    </tr>
                @endforeach
                <tr><td colspan="3" style="height:10px;"></td></tr>
            </table>
        </td>
    </tr>
@if($description)
    <tr>
        <td style="width:48px;"></td>
        <td>
            <table cellpadding="0" cellspacing="0" border="0" style="width:600px;">
                <tr>
                    <td style="font-size:10px; color:#333333;width:120px"><strong>Description</strong></td>
                    <td style="font-size:8px; color:#666666; width:400px;">{{ $description }}</td>
                </tr>
            </table>
            <table cellpadding="1" cellspacing="0" border="0" style="width:500px;">
                <tr><td colspan="3" style="height:10px;"></td></tr>
            </table>
        </td>
    </tr>
@endif
@if($abstract)
    <tr>
        <td style="width:48px;"></td>
        <td>
            <table cellpadding="0" cellspacing="0" border="0" style="width:600px;">
                <tr>
                    <td style="font-size:10px; color:#333333;width:120px"><strong>Abstract</strong></td>
                    <td style="font-size:8px; color:#666666; width:400px;">{{ $abstract }}</td>
                </tr>
            </table>
            <table cellpadding="1" cellspacing="0" border="0" style="width:500px;">
                <tr><td colspan="3" style="height:10px;"></td></tr>
            </table>
        </td>
    </tr>
@endif
@if($notes)
    <tr>
        <td style="width:48px;"></td>
        <td>
            <table cellpadding="0" cellspacing="0" border="0" style="width:600px;">
                <tr>
                    <td style="font-size:10px; color:#333333;width:120px"><strong>Notes</strong></td>
                    <td style="font-size:8px; color:#666666; width:400px;">{{ $notes }}</td>
                </tr>
            </table>
            <table cellpadding="1" cellspacing="0" border="0" style="width:500px;">
                <tr><td colspan="3" style="height:10px;"></td></tr>
            </table>
        </td>
    </tr>
@endif
    <tr>
        <td style="width:48px;"></td>
        <td>
            <table cellpadding="0" cellspacing="0" border="0" style="width:600px;">
                <tr>
                    <td style="font-size:10px; color:#333333;width:120px"><strong>Direct download link</strong></td>
                    <td style="font-size:8px; color:#666666; width:400px;"><a href="{{ $downloadUrl }}" style="color:#1d496a;">{{ $downloadUrl }}</a></td>
                </tr>
            </table>
            <table cellpadding="1" cellspacing="0" border="0" style="width:500px;">
                <tr><td colspan="3" style="height:10px;"></td></tr>
            </table>
        </td>
    </tr>
    <tr>
        <td style="width:48px;"></td>
        <td>
            <table cellpadding="0" cellspacing="0" border="0" style="width:600px;">
                <tr>
                    <td style="font-size:10px; color:#333333;width:120px"><strong>Source</strong></td>
                    <td style="font-size:8px; color:#666666; width:400px;">{{ $digital_source }}</td>
                </tr>
            </table>
            <table cellpadding="1" cellspacing="0" border="0" style="width:500px;">
                <tr><td colspan="3" style="height:100px;"></td></tr>
            </table>
        </td>
    </tr>
    <tr>
        <td style="width:48px;"></td>
        <td style="text-align: center">
            <table cellpadding="0" cellspacing="0" border="0" style="width:800px;">
                <tr>
                    <td style="font-size:8px; color:#666666; width:550px; text-align: center">This digital document is disseminated and preserved by the FSM National VLA Curriculum Resource Library: https://micronesian.school<br/> It is protected by copyright law and remains the property of the rights-holders. Contact irei@islandresearch.org for inquiries.</td>
                </tr>
            </table>
            <table cellpadding="1" cellspacing="0" border="0" style="width:500px;">
                <tr><td colspan="3" style="height:10px;"></td></tr>
            </table>
        </td>
    </tr>
</table>
