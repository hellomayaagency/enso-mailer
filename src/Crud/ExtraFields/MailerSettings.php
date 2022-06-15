<?php

namespace Hellomayaagency\Enso\Mailer\Crud\ExtraFields;

use Illuminate\Support\Facades\App;
use Yadda\Enso\Crud\Forms\CollectionSection;
use Yadda\Enso\Crud\Forms\Fields\ImageUploadField;
use Yadda\Enso\Crud\Forms\Fields\TextField;
use Yadda\Enso\Crud\Forms\Form;
use Yadda\Enso\Settings\Contracts\ExtraSettings;

/**
 * Provides Mailer package settings for Ensō
 */
class MailerSettings implements ExtraSettings
{
    /**
     * Take a form and process it by adding, removing
     * or updating sections or fields.
     *
     * @param Form $form
     *
     * @return Form
     */
    public static function updateForm(Form $form): Form
    {
        $settings = App::get('ensosettings');
        $fallback_site_name = $settings->get('site-name');

        $section = CollectionSection::make('mailer')
            ->addFields([
                ImageUploadField::make('mailer_header_image'),
                ImageUploadField::make('mailer_company_logo')
                    ->addFieldsetClass('is-6'),
                TextField::make('mailer_company_name')
                    ->setHelpText(
                        'This will be used as a fallback when there is no company logo. ' .
                            'If both are empty, it will fall back to the Site Name, which is ' .
                            'currently set as `' . $fallback_site_name . '`'
                    )
                    ->addFieldsetClass('is-6'),
                TextField::make('mailer_company_copyright')
                    ->setHelpText('Leaving this blank will hide the copyright notice')
                    ->addFieldsetClass('is-4'),
                TextField::make('mailer_company_footer_name')
                    ->setHelpText('Leaving this blank will fall back to Site Name')
                    ->addFieldsetClass('is-4'),
                TextField::make('mailer_company_email')
                    ->setHelpText('Leaving this blank will fall back to the Administrator Email')
                    ->addFieldsetClass('is-4'),
            ]);

        /**
         * Mailer content it likely not needed to be updated with any regularity,
         * but probably more so than updating site analytics settings or meta.
         */
        if ($form->hasSection('analytics')) {
            $form->addSectionBefore('analytics', $section);
        } elseif ($form->hasSection('meta')) {
            $form->addSectionBefore('meta', $section);
        } else {
            $form->addSection($section);
        }

        return $form;
    }
}
