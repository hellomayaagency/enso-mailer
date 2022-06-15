
[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/support-ukraine.svg?t=1" />](https://supportukrainenow.org)

# Enso Mailer package

[![Latest Version on Packagist](https://img.shields.io/packagist/v/hellomayaagency/enso-mailer.svg?style=flat-square)](https://packagist.org/packages/hellomayaagency/enso-mailer)
[![GitHub Tests Action Status](https://img.shields.io/github/workflow/status/hellomayaagency/enso-mailer/run-tests?label=tests)](https://github.com/hellomayaagency/enso-mailer/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/workflow/status/hellomayaagency/enso-mailer/Check%20&%20fix%20styling?label=code%20style)](https://github.com/hellomayaagency/enso-mailer/actions?query=workflow%3A"Check+%26+fix+styling"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/hellomayaagency/enso-mailer.svg?style=flat-square)](https://packagist.org/packages/hellomayaagency/enso-mailer)

A Mailer package for Ensō. This will let you build Audiences from your users based on custom queries, then build and send mail to these audiences.

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/enso-mailer.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/enso-mailer)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Installation

You can install the package via composer:

```bash
composer require hellomayaagency/enso-mailer
```

You can optionally publish migrations:

```bash
php artisan vendor:publish --provider "Hellomayaagency\\Enso\\Mailer\\EnsoMailerServiceProvider" --tag "enso-migrations"
```

Whether you publish them or not, you can then run them with:

```bash
php artisan migrate
```

You can publish the config file with:

```bash
php artisan vendor:publish --provider Hellomayaagency\\Enso\\Mailer\\EnsoMailerServiceProvider --tag="enso-mailer-config"
```

This is the contents of the published config file:

```php
return [
];
```

Optionally, you can publish the views using

```bash
php artisan vendor:publish --tag="enso-mailer-views"
```

## Usage

```php
$ensoMailer = new Hellomayaagency\EnsoMailer();
echo $ensoMailer->echoPhrase('Hello, Hellomayaagency!');
```

## Testing

```bash
composer test
```
### Ngrok

You need to:

1. Run ngrok via for a homestead build: `ngrok http 192.168.10.10:80 -host-header=site.test`
1. Fill the environment variable `ENSO_MAILER_MANDRILL_URL` with the ngrok url gives you.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](https://github.com/spatie/.github/blob/main/CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Andrew Ellender](https://github.com/hellomayaagency)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
