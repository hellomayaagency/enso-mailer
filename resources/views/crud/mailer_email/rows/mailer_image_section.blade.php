@if($row->image)
<table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnImageBlock" style="min-width:100%;">
  <tbody class="mcnImageBlockOuter">
    <tr>
      <td valign="top" style="padding:0px" class="mcnImageBlockInner">
        <table align="left" width="100%" border="0" cellpadding="0" cellspacing="0" class="mcnImageContentContainer" style="min-width:100%;">
          <tbody>
            <tr>
              <td class="mcnImageContent" valign="top" style="padding-right: 0px; padding-left: 0px; padding-top: 0; padding-bottom: 0; text-align:center;">
                @if(!isset($message))
                  <img align="center" alt="" src="{{ $row->image->getResizeUrl('mailer_image', true) }}" width="600" style="max-width:1200px; padding-bottom: 0; display: inline !important; vertical-align: bottom;" class="mcnRetinaImage">
                @else
                  {{-- <img align="center" alt="" src="{{ $message->embed() }}" width="600" style="max-width:1200px; padding-bottom: 0; display: inline !important; vertical-align: bottom;" class="mcnRetinaImage"> --}}
                @endif
              </td>
            </tr>
          </tbody>
        </table>
      </td>
    </tr>
  </tbody>
</table>
@endif
