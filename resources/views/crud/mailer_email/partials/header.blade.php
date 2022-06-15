<tr>
  @if(EnsoMailer::hasMailHeaderImage())
    @if(empty($message))
      <td align="center" valign="top" id="templateHeader" data-template-container style="background-image:url('{{ EnsoMailer::getMailHeaderImage() }}')">
    @else
      <td align="center" valign="top" id="templateHeader" data-template-container style="background-image:url('{{ $message->embed(EnsoMailer::getMailHeaderImage('path')) }}')">
    @endif
  @else
    <td align="center" valign="top" id="templateHeader" data-template-container>
  @endif
    <!--[if (gte mso 9)|(IE)]>
    <table align="center" border="0" cellspacing="0" cellpadding="0" width="600" style="width:600px;">
    <tr>
    <td align="center" valign="top" width="600" style="width:600px;">
    <![endif]-->
    <table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" class="templateContainer">
      <tbody>
        <tr>
          <td valign="top" class="headerContainer">
            @if(EnsoMailer::hasMailCompanyLogo())
              <table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnImageBlock" style="min-width:100%;">
                <tbody class="mcnImageBlockOuter">
                  <tr>
                    <td valign="top" style="padding:9px" class="mcnImageBlockInner">
                      <table align="left" width="100%" border="0" cellpadding="0" cellspacing="0" class="mcnImageContentContainer" style="min-width:100%;">
                        <tbody>
                          <tr>
                            <td class="mcnImageContent" valign="top" style="padding-right: 9px; padding-left: 9px; padding-top: 0; padding-bottom: 0; text-align:center;">
                              @if(empty($message))
                                <img align="center" alt="" src="{{ EnsoMailer::getMailCompanyLogo() }}" width="270" style="max-width:540px; padding-bottom: 0; display: inline !important; vertical-align: bottom;" class="mcnRetinaImage">
                              @else
                                <img align="center" alt="" src="{{ $message->embed(EnsoMailer::getMailCompanyLogo('path')) }}" width="270" style="max-width:540px; padding-bottom: 0; display: inline !important; vertical-align: bottom;" class="mcnRetinaImage">
                              @endif
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </td>
                  </tr>
                </tbody>
              @else
                <table align="left" border="0" cellpadding="0" cellspacing="0" style="max-width:100%; min-width:100%;" width="100%" class="mcnTextContentContainer">
                  <tbody>
                    <tr>
                      <td valign="top" class="mcnTextContent" style="padding-top:0; padding-right:18px; padding-bottom:9px; padding-left:18px;">
                        @if(EnsoMailer::hasMailCompanyTitle())
                          <h1 class="title is-1" style="text-align: center;">{{ EnsoMailer::getMailCompanyTitle() }}</h1>
                        @endif
                      </td>
                    </tr>
                  </tbody>
                </table>
              @endif
            </table>
          </td>
        </tr>
      </tbody>
    </table>
    <!--[if (gte mso 9)|(IE)]>
    </td>
    </tr>
    </table>
    <![endif]-->
  </td>
</tr>
