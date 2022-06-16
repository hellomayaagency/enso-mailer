<tr>
<td align="center" valign="top" id="templateFooter" data-template-container>
<!--[if (gte mso 9)|(IE)]>
<table align="center" border="0" cellspacing="0" cellpadding="0" width="600" style="width:600px;">
<tr>
<td align="center" valign="top" width="600" style="width:600px;">
<![endif]-->
<table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" class="templateContainer">
<tbody>
<tr>
<td valign="top" class="footerContainer">
@if(EnsoMailer::displayFooterTop())
@if (EnsoMailer::hasMailCompanyLogo())
<table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnImageBlock" style="min-width:100%;">
<tbody class="mcnImageBlockOuter">
<tr>
<td valign="top" style="padding:9px" class="mcnImageBlockInner">
<table align="left" width="100%" border="0" cellpadding="0" cellspacing="0" class="mcnImageContentContainer" style="min-width:100%;">
<tbody>
<tr>
<td class="mcnImageContent" valign="top" style="padding-right: 9px; padding-left: 9px; padding-top: 0; padding-bottom: 0; text-align:center;">
<a href="http://marmot-tours.co.uk/" title="" class="" target="_blank">
@if(empty($message))
<img align="center" alt="" src="{{ EnsoMailer::getMailCompanyLogo() }}" width="90" style="max-width:180px; padding-bottom: 0; display: inline !important; vertical-align: bottom;" class="mcnRetinaImage">
@else
<img align="center" alt="" src="{{ $message->embed(EnsoMailer::getMailCompanyLogo('path')) }}" width="90" style="max-width:180px; padding-bottom: 0; display: inline !important; vertical-align: bottom;" class="mcnRetinaImage">
@endif
</a>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
@endif
@if (EnsoMailer::hasSocialTemplate())
<table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnFollowBlock" style="min-width:100%;">
<tbody class="mcnFollowBlockOuter">
<tr>
<td align="center" valign="top" style="padding:9px" class="mcnFollowBlockInner">
<table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnFollowContentContainer" style="min-width:100%;">
<tbody>
<tr>
<td align="center" style="padding-left:9px;padding-right:9px;">
<table border="0" cellpadding="0" cellspacing="0" width="100%" style="min-width:100%;" class="mcnFollowContent">
<tbody>
<tr>
<td align="center" valign="top" style="padding-top:9px; padding-right:9px; padding-left:9px;">
<table align="center" border="0" cellpadding="0" cellspacing="0">
<tbody>
<tr>
<td align="center" valign="top">
<!--[if mso]>
<table align="center" border="0" cellspacing="0" cellpadding="0">
<tr>
<![endif]-->
@include('enso-crud::mailer_email.partials.social-icon-list')
<!--[if mso]>
</tr>
</table>
<![endif]-->
</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
</td>
</tr>
</tbody>
</table>
@endif
@include('enso-crud::mailer_email.rows.mailer_divider_section')
@endif
<table border="0" cellpadding="0" cellspacing="0" width="100%" class="mcnTextBlock" style="min-width:100%;">
<tbody class="mcnTextBlockOuter">
<tr>
<td valign="top" class="mcnTextBlockInner" style="padding-top:9px;">
<!--[if mso]>
<table align="left" border="0" cellspacing="0" cellpadding="0" width="100%" style="width:100%;">
<tr>
<![endif]-->
<!--[if mso]>
<td valign="top" width="600" style="width:600px;">
<![endif]-->
<table align="left" border="0" cellpadding="0" cellspacing="0" style="max-width:100%; min-width:100%;" width="100%" class="mcnTextContentContainer">
<tbody>
<tr>
<td valign="top" class="mcnTextContent" style="padding-top:0; padding-right:18px; padding-bottom:9px; padding-left:18px;">
@if(EnsoMailer::hasMailCompanyCopyright())
<em>Copyright © {{ EnsoMailer::getMailCompanyCopyright() }} {{ EnsoMailer::getMailCompanyFooterName() }}, All rights reserved.</em>
<br>
<br>
@endif
@if(EnsoMailer::hasMailCompanyEmail())
<strong>Our mailing address is:</strong>
<br>
<a href="mailto:{{ EnsoMailer::getMailCompanyEmail() }}">{{ EnsoMailer::getMailCompanyEmail() }}</a>
<br>
<br>
@endif
@include('enso-crud::mailer_email.partials.footer-bottom')
</td>
</tr>
</tbody>
</table>
<!--[if mso]>
</td>
<![endif]-->
<!--[if mso]>
</tr>
</table>
<![endif]-->
</td>
</tr>
</tbody>
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
