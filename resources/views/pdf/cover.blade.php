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
    <tr>
        {{-- Left side with text (using table cellpadding for spacing instead of CSS padding) --}}
        <td style="color:#ffffff; width:435px">
            <table cellpadding="0" cellspacing="0" border="0">
                <tr>
                    <td style="width:12px;"></td>
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
{{-- Generated date bar --}}
<table cellpadding="3" cellspacing="0" style="width:1000px; background-color:#f4f4f4;">
    <tr>
        <td style="text-align:left; font-size:8px; color:#888888;">
            <table cellpadding="0" cellspacing="0" border="0" style="width:1000px;">
                <tr>
                    <td style="width:12px;"></td>
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
        <td style="width:12px;"></td>
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
                        <td style="font-size:14px; color:#888888; width: 100px">Translated title:</td>
                        <td style="font-size:14px;">{{ $subtitle }}</td>
                    </tr>
                @endif
            </table><br><br><table cellpadding="0" cellspacing="0" border="0" style="width:600px;">
                <tr>
                    <td  style="width: 100px">
                        <table cellspacing="0" border="0">
                            <tr><td><span style="color:#666666; font-size: 10px">{{ $metaFirst['label'] }}:</span></td></tr>
                            <tr><td><span style="color:#333333; font-size: 12px"><strong>{{ $metaFirst['value'] }}</strong></span></td></tr>
                        </table>
                    </td>
                    <td  style="width: 500px">
                        <table cellspacing="0" border="0">
                            <tr><td><span style="color:#666666; font-size: 10px">{{ $metaSecond['label'] }}:</span></td></tr>
                            <tr><td><span style="color:#333333; font-size: 12px"><strong>{{ $metaSecond['value'] }}</strong></span></td></tr>
                        </table>
                    </td>
                </tr>
            </table><br><br><table cellpadding="0" cellspacing="0" border="0" style="width:600px;">
                <tr>
                    <td  style="width: 100px">
                        <table cellspacing="0" border="0">
                            <tr><td><span style="color:#666666; font-size: 10px">{{ $metaThird['label'] }}:</span></td></tr>
                            <tr><td><span style="color:#333333; font-size: 12px"><strong>{{ $metaThird['value'] }}</strong></span></td></tr>
                        </table>
                    </td>
                    <td  style="width: 500px">
                        <table cellspacing="0" border="0">
                            <tr><td><span style="color:#666666; font-size: 10px">{{ $metaForth['label'] }}:</span></td></tr>
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
        <td style="width:12px;"></td>
        <td>
            <table cellpadding="0" cellspacing="0" border="0" style="width:600px;">
                <tr><td style="font-size:10px; color:#333333;width:100px"><strong>Contributors</strong></td></tr>
            </table>
            <table cellpadding="1" cellspacing="0" border="0" style="width:500px;">
                @foreach($contributors as $row)<tr><td style="font-size:8px; color:#666666; width:100px;">{{ $row['label'] }}:</td><td style="font-size:8px; color:#333333;">{{ $row['value'] }}</td></tr>@endforeach
                <tr><td colspan="3" style="height:10px;"></td></tr>
            </table>
        </td>
    </tr>
    <tr>
        <td style="width:12px;"></td>
        <td>
            <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                <tr>
                    <td style="font-size:10px; color:#333333;"><strong>Edition notes</strong></td>
                </tr>
            </table>
            {{-- Border line (replaces border-bottom CSS) --}}
            <table cellpadding="0" cellspacing="0" border="0" style="width:100%; background-color:#cccccc;">
                <tr>
                    <td style="height:1px;"></td>
                </tr>
            </table>
            {{-- Spacer row (replaces padding-bottom:2px) --}}
            <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                <tr>
                    <td style="height:2px;"></td>
                </tr>
            </table>
            <table cellpadding="1" cellspacing="0" border="0" style="width:100%;">
                @foreach($editionNotes as $row)
                    <tr>
                        <td style="font-size:8px; color:#666666; width:90px;">{{ $row['label'] }}:</td>
                        <td style="font-size:8px; color:#333333;">{{ $row['value'] }}</td>
                    </tr>
                @endforeach
                {{-- Spacer row (replaces margin-bottom:6px) --}}
                <tr>
                    <td colspan="2" style="height:6px;"></td>
                </tr>
            </table>

            {{-- Classification section --}}
            <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                <tr>
                    <td style="font-size:10px; color:#333333;"><strong>Classification</strong></td>
                </tr>
            </table>
            {{-- Border line (replaces border-bottom CSS) --}}
            <table cellpadding="0" cellspacing="0" border="0" style="width:100%; background-color:#cccccc;">
                <tr>
                    <td style="height:1px;"></td>
                </tr>
            </table>
            <table cellpadding="1" cellspacing="0" border="0" style="width:100%;">
                @foreach($classifications as $row)
                    <tr>
                        <td style="font-size:8px; color:#666666; width:90px;">{{ $row['label'] }}:</td>
                        <td style="font-size:8px; color:#333333;">{{ $row['value'] }}</td>
                    </tr>
                @endforeach
            </table>

            {{-- Description section --}}
            @if($description)
                <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                    <tr>
                        <td style="font-size:10px; color:#333333;"><strong>Description</strong></td>
                    </tr>
                </table>
                <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                    <tr>
                        <td style="font-size:8px; color:#333333; line-height:1.4;">{{ $description }}</td>
                    </tr>
                </table>
            @endif

            {{-- Abstract section --}}
            @if($abstract)
                <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                    <tr>
                        <td style="font-size:10px; color:#333333;"><strong>Abstract</strong></td>
                    </tr>
                </table>
                {{-- Border line (replaces border-bottom CSS) --}}
                <table cellpadding="0" cellspacing="0" border="0" style="width:100%; background-color:#cccccc;">
                    <tr>
                        <td style="height:1px;"></td>
                    </tr>
                </table>
                {{-- Spacer row (replaces padding-bottom:2px) --}}
                <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                    <tr>
                        <td style="height:2px;"></td>
                    </tr>
                </table>
                <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                    <tr>
                        <td style="font-size:8px; color:#333333; line-height:1.4;">{{ $abstract }}</td>
                    </tr>
                    {{-- Spacer row (replaces margin-bottom:6px) --}}
                    <tr>
                        <td style="height:6px;"></td>
                    </tr>
                </table>
            @endif

            {{-- Notes section --}}
            @if($notes)
                <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                    <tr>
                        <td style="font-size:10px; color:#333333;"><strong>Notes</strong></td>
                    </tr>
                </table>
                {{-- Border line (replaces border-bottom CSS) --}}
                <table cellpadding="0" cellspacing="0" border="0" style="width:100%; background-color:#cccccc;">
                    <tr>
                        <td style="height:1px;"></td>
                    </tr>
                </table>
                {{-- Spacer row (replaces padding-bottom:2px) --}}
                <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                    <tr>
                        <td style="height:2px;"></td>
                    </tr>
                </table>
                <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                    <tr>
                        <td style="font-size:8px; color:#333333; line-height:1.4;">{{ $notes }}</td>
                    </tr>
                    {{-- Spacer row (replaces margin-bottom:6px) --}}
                    <tr>
                        <td style="height:6px;"></td>
                    </tr>
                </table>
            @endif

            {{-- Direct download link section --}}
            <table cellpadding="0" cellspacing="0" border="0" style="width:100px;">
                <tr>
                    <td style="font-size:10px; color:#333333;"><strong>Direct download link</strong></td>
                </tr>
            </table>
            {{-- Border line (replaces border-bottom CSS) --}}
            <table cellpadding="0" cellspacing="0" border="0" style="width:100%; background-color:#cccccc;">
                <tr>
                    <td style="height:1px;"></td>
                </tr>
            </table>
            {{-- Spacer row (replaces padding-bottom:2px) --}}
            <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                <tr>
                    <td style="height:2px;"></td>
                </tr>
            </table>
            <table cellpadding="4" cellspacing="0" border="0" style="width:100%; background-color:#e8f0f5;">
                <tr>
                    <td style="font-size:7px; color:#1d496a;">{{ $downloadUrl }}</td>
                </tr>
                {{-- Spacer row (replaces margin-bottom:6px) --}}
                <tr>
                    <td style="height:6px; background-color:transparent;"></td>
                </tr>
            </table>

            {{-- Source section --}}
            <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                <tr>
                    <td style="font-size:10px; color:#333333;"><strong>Source</strong></td>
                </tr>
            </table>
            {{-- Border line (replaces border-bottom CSS) --}}
            <table cellpadding="0" cellspacing="0" border="0" style="width:100%; background-color:#cccccc;">
                <tr>
                    <td style="height:1px;"></td>
                </tr>
            </table>
            {{-- Spacer row (replaces padding-bottom:2px) --}}
            <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                <tr>
                    <td style="height:2px;"></td>
                </tr>
            </table>
            <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                <tr>
                    <td style="font-size:8px; color:#333333; line-height:1.4;">Digital document added to the VLA
                        resource library. If you have copyright concerns or need a hard copy, please email
                        irei@islandresearch.org for more information.
                    </td>
                </tr>
                {{-- Spacer row (replaces margin-bottom:5px) --}}
                <tr>
                    <td style="height:5px;"></td>
                </tr>
            </table>

            {{-- Note box with simulated border --}}
            {{-- Outer table creates 1px border effect with background color --}}
            <table cellpadding="1" cellspacing="0" border="0" style="width:100%; background-color:#dddddd;">
                <tr>
                    <td>
                        {{-- Inner table with actual content background --}}
                        <table cellpadding="5" cellspacing="0" border="0" style="width:100%; background-color:#f5f5f5;">
                            <tr>
                                <td style="font-size:7px; color:#666666; line-height:1.4;">
                                    This digital document is disseminated and preserved by the FSM National VLA Curriculum Resource
                                    Library: https://micronesian.school<br/><br/>
                                    It is protected by copyright law and remains the property of the rights-holders. Contact
                                    irei@islandresearch.org for inquiries.
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>

            {{-- Vertical spacer (replaces bottom padding) --}}
            <table cellpadding="0" cellspacing="0" border="0" style="width:100%;">
                <tr>
                    <td style="height:12px;"></td>
                </tr>
            </table>

        </td>
        <td style="width:35px;"></td>
    </tr>
</table>

{{-- Footer text over gradient (gradient drawn by service at y=264) --}}
{{-- This needs absolute positioning which TCPDF doesn't support well in HTML --}}
{{-- Footer gradient and text will be added via service --}}
