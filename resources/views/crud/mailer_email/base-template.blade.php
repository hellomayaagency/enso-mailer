<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
<!-- NAME: SELL PRODUCTS -->
<!--[if gte mso 15]>
<xml>
<o:OfficeDocumentSettings>
<o:AllowPNG/>
<o:PixelsPerInch>96</o:PixelsPerInch>
</o:OfficeDocumentSettings>
</xml>
<![endif]-->
<meta charset="UTF-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="x-apple-disable-message-reformatting"/>

<title>{{ $mailable->getSubject() }}</title>

<!-- Styles -->
@include('enso-crud::mailer_email.fonts')
<link href="{{ asset(mix('css/enso-mail.css')) }}" rel="stylesheet">
</head>
<body>
<center>
<table align="center" border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" id="bodyTable" styles="font-family:Verdana, Geneva, sans-serif" role="presentation">
<tr>
<td align="center" valign="top" id="bodyCell">
<!-- BEGIN TEMPLATE // -->
<table border="0" cellpadding="0" cellspacing="0" width="100%">

@include('enso-crud::mailer_email.partials.header')

<tr>
<td align="center" valign="top" id="templateBody" data-template-container>
<!--[if (gte mso 9)|(IE)]>
<table align="center" border="0" cellspacing="0" cellpadding="0" width="600" style="width:600px;">
<tr>
<td align="center" valign="top" width="600" style="width:600px;">
<![endif]-->
<table align="center" border="0" cellpadding="0" cellspacing="0" width="100%" class="templateContainer">
<tr>
<td align="center" valign="top" class="bodyContainer">

@yield('email-title')

@yield('email-body')

</td>
</tr>
</table>
<!--[if (gte mso 9)|(IE)]>
</td>
</tr>
</table>
<![endif]-->
</td>
</tr>

@include('enso-crud::mailer_email.partials.footer-row')

</table>
<!-- // END TEMPLATE -->
</td>
</tr>
</table>
</center>
</body>
</html>
