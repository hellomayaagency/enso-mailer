@if($row->image)
<table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnImageBlock" style="min-width:100%; {{ $first_row ? 'padding-top:30px !important;' : '' }}">
<tbody class="mcnImageBlockOuter">
<tr>
<td align="center" valign="top" style="padding:0px" class="mcnImageBlockInner">
<table align="center" width="100%" border="0" cellpadding="0" cellspacing="0" class="mcnImageContentContainer" style="min-width:100%;">
<tbody>
<tr>
<td align="center" class="mcnImageContent" valign="top" style="padding-right: 0px; padding-left: 0px; padding-top: 0; padding-bottom: 0; text-align:center;">
@if(!isset($message))
@if ($image->getFileinfoProperty('width') < 600)
<img align="center" title="{{ $image->title }}" alt="{{ $image->alt_text }}" src="{{ $image->getUrl() }}" width="{{ $image->getFileinfoProperty('width') }}" style="max-width:1200px; padding-bottom: 0; display: inline !important; vertical-align: bottom;" class="mcnRetinaImage">
@else
<img align="center" title="{{ $image->title }}" alt="{{ $image->alt_text }}" src="{{ $image->getResizeUrl('mailer_image', true) }}" width="600" style="max-width:1200px; padding-bottom: 0; display: inline !important; vertical-align: bottom;" class="mcnRetinaImage">
@endif
@else
<img align="center" title="{{ $image->title }}" alt="{{ $image->alt_text }}" src="{{ $message->embed($image->getPath()) }}" width="600" style="max-width:1200px; padding-bottom: 0; display: inline !important; vertical-align: bottom;" class="mcnRetinaImage">
@endif
</td>
</tr>
@if(!empty($image->caption))
<tr>
<td align="center">
<p class="mcnCaption">{{$image->caption}}</p>
</td>
</tr>
@endif
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
@endif
