@if($row->link && $row->text)
<table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnButtonBlock" style="min-width:100%; {{ $first_row ? 'padding-top:30px !important;' : '' }}">
<tbody class="mcnButtonBlockOuter">
<tr>
<td style="padding-top:0; padding-right:18px; padding-bottom:18px; padding-left:18px;" valign="top" align="left" class="mcnButtonBlockInner">
<table border="0" cellpadding="0" cellspacing="0" class="mcnButtonContentContainer" style="border-collapse: separate !important;border: 2px solid #0B2532;border-radius: 3px;">
<tbody>
<tr>
<td align="center" valign="middle" class="mcnButtonContent" style="font-family: Arvo, Courier, Georgia, serif; font-size: 14px; padding: 15px;">
<a class="mcnButton " title="{{ $row->text }}" href="{{ $row->link }}" target="{{ $row->target ?? '_self' }}" style="font-weight: normal;letter-spacing: normal;line-height: 100%;text-align: center;text-decoration: none;color: #0B2532;">{{ $row->text }}</a>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
@endif
