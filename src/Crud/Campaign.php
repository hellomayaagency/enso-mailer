<?php

namespace Hellomayaagency\Enso\Mailer\Crud;

use Hellomayaagency\Enso\Mailer\Contracts\Audience;
use Hellomayaagency\Enso\Mailer\Contracts\Campaign as CampaignContract;
use Hellomayaagency\Enso\Mailer\Crud\Forms\Sections\CTASection;
use Hellomayaagency\Enso\Mailer\Crud\Forms\Sections\DividerSection;
use Hellomayaagency\Enso\Mailer\Crud\Forms\Sections\ImageSection;
use Hellomayaagency\Enso\Mailer\Crud\Forms\Sections\TextSection;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Yadda\Enso\Crud\Config;
use Yadda\Enso\Crud\Forms\Fields\BelongsToManyField;
use Yadda\Enso\Crud\Forms\Fields\DateField;
use Yadda\Enso\Crud\Forms\Fields\EmailField;
use Yadda\Enso\Crud\Forms\Fields\FlexibleContentField;
use Yadda\Enso\Crud\Forms\Fields\SlugField;
use Yadda\Enso\Crud\Forms\Fields\TextField;
use Yadda\Enso\Crud\Forms\Form;
use Yadda\Enso\Crud\Forms\Section;
use Yadda\Enso\Crud\Tables\Text;

class Campaign extends Config
{
    /**
     * Defines the available FlexibleContentSections and their names for
     * a Campaign.
     *
     * @var array
     */
    protected static $email_body_sections = [
        'mail_body' => [
            'mailer_image_section' => ImageSection::class,
            'mailer_text_section' => TextSection::class,
            'mailer_button_section' => CTASection::class,
            'mailer_divider_section' => DividerSection::class,
        ],
    ];

    public function configure()
    {
        $this->setModel(get_class(App::make(CampaignContract::class)))
            ->setRoute('admin.mailer.campaigns')
            ->setViewsDir('mailer_campaigns')
            ->setNameSingular('Campaign')
            ->setNamePlural('Campaigns')
            ->setPaginate(true)
            ->setPerPage(20)
            ->setPreloadRelationships(['stats'])
            ->setTableColumns(array_merge([
                (new Text('name')),
                (new Text('subject')),
            ], $this->getIndexStats()))
            ->setRules([]);

        $this->addIndexActions([
            'send' => [
                'component' => 'link-button',
                'route' => '/%ID%/status',
                'title' => 'Send',
                'wrapperClass' => 'button is-success',
                'only_when' => function ($item) {
                    return ! $item->hasBeenSent();
                },
                'button' => [
                    'type' => 'fa',
                    'content' => 'fa fa-paper-plane',
                ],
                'order' => 6,
            ],
            'show' => [
                'component' => 'link-button',
                'route' => '/%ID%/users',
                'title' => 'User list',
                'wrapperClass' => 'button',
                'button' => [
                    'type' => 'fa',
                    'content' => 'fa fa-users',
                ],
                'only_when' => function ($item) {
                    return ! $item->hasBeenSent();
                },
                'order' => 7,
            ],
            'preview' => [
                'component' => 'link-button',
                'route' => '/%ID%/preview',
                'title' => 'Preview',
                'wrapperClass' => 'button',
                'button' => [
                    'type' => 'fa',
                    'content' => 'fa fa-eye',
                ],
                'order' => 8,
            ],
            'status' => [
                'component' => 'link-button',
                'route' => '/%ID%/status',
                'title' => 'Status',
                'wrapperClass' => 'button',
                'only_when' => function ($item) {
                    return $item->hasBeenSent();
                },
                'button' => [
                    'type' => 'fa',
                    'content' => 'fa fa-line-chart',
                ],
                'order' => 9,
            ],
        ]);

        $this->getIndexAction('edit')->mergeCondition(function ($item) {
            return ! $item->hasBeenSent();
        });

        $this->getIndexAction('delete')->mergeCondition(function ($item) {
            return ! $item->hasBeenSent();
        });
    }

    /**
     * Gets some stats to show on the index page.
     *
     * @return array
     */
    protected function getIndexStats()
    {
        return [
            (new Text('sends'))
                ->setLabel('Sends')
                ->setProps(['class' => 'has-text-centered'])
                ->setThClasses('is-narrow'),
            (new Text('unique_opens'))
                ->setLabel('Opens')
                ->setProps(['class' => 'has-text-centered'])
                ->setThClasses('is-narrow'),
            (new Text('unique_clicks'))
                ->setLabel('Clicks')
                ->setProps(['class' => 'has-text-centered'])
                ->setThClasses('is-narrow'),
            (new Text('failed_sends'))
                ->setLabel('Fails')
                ->setProps(['class' => 'has-text-centered'])
                ->setThClasses('is-narrow'),
        ];
    }

    /**
     * Default form configuration.
     *
     * @param \Yadda\Enso\Crud\Forms\Form $form
     *
     * @return \Yadda\Enso\Crud\Forms\Form
     */
    public function create(Form $form)
    {
        $audience_class = get_class(App::make(Audience::class));

        $from_name = App::make(CampaignContract::class)::getSenderNameFallback() ?? '';
        $from_email = App::make(CampaignContract::class)::getSenderEmailFallback() ?? '';

        $form->addSections([
            1 => (new Section('main'))
                ->addFields([
                    1 => (new TextField('name'))
                        ->setHelpText('This is for internal reference only'),
                    2 => (new SlugField('slug'))
                        ->setSource('name')
                        ->setRoute('%SLUG%'),
                    3 => (new TextField('subject'))
                        ->setLabel('Email Subject'),
                    4 => (new TextField('from_name'))
                        ->setLabel('`From` Name')
                        ->addFieldsetClass('is-6')
                        ->setPlaceholder($from_name)
                        ->setHelpText('Leaving this blank will use the default name, currently `' . $from_name . '`'),
                    5 => (new EmailField('from_email'))
                        ->setLabel('`From` Email')
                        ->addFieldsetClass('is-6')
                        ->setPlaceholder($from_email)
                        ->setHelpText('Leaving this blank will use the default email, currently `' . $from_email . '`'),
                    6 => (new BelongsToManyField('audiences'))
                        ->useAjax(route('admin.mailer.audiences.list'), $audience_class)
                        ->setHelpText(
                            'This is a list of Audiences to send your Campaign to. ' .
                                'If a user is in multiple Audiences, they will still only receive one email.'
                        ),
                ]),
            2 => (new Section('content'))
                ->addFields([
                    1 => (new TextField('mail_title'))
                        ->setHelpText(
                            'This is the Title of your email. Leaving this field and the date ' .
                                'field blank will hide the title row, and just display content'
                        )
                        ->addFieldsetClass('is-6'),
                    2 => (new DateField('mail_date'))
                        ->setHelpText(
                            'This is the Date to add under the email title. ' .
                                'It will display in a format like `30th Jun 2018`'
                        )
                        ->addFieldsetClass('is-6'),
                    3 => (new FlexibleContentField('mail_body'))
                        ->setLabel('Mail Body')
                        ->addRowSpecs(
                            self::getRowSpecSections('mail_body')
                        ),
                ]),
        ]);

        return $form;
    }

    /**
     * Parses Content from a saved field, based on the Rowspecs that field should
     * have available.
     *
     * Format for each RowSpec should follow the structure:
     * [
     *   'type' => 'row_spec_name',
     *   'content => [ ...content ]
     * ]
     *
     * @param string $field
     * @param array  $content
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getRowSpecContent($field, $content)
    {
        $rowspec_definitions = self::getRowSpecDefinitions($field);

        return collect($content)->map(function ($flexible_row_content) use ($rowspec_definitions) {
            $row_type = Arr::get($flexible_row_content, 'type');

            if (! array_key_exists($row_type, $rowspec_definitions)) {
                return null;
            }

            return (new $rowspec_definitions[$row_type]())->unpack($flexible_row_content);
        })->filter();
    }

    /**
     * Gets the RowSpec Definitions for a given field
     *
     * @param string $field
     *
     * @return array
     */
    protected static function getRowSpecDefinitions($field)
    {
        return self::$email_body_sections[$field];
    }

    /**
     * Instantiates a set of FlexibleContentSections to add as RowSpecs to a
     * FlexibleContentField, for the given field name.
     *
     * @param string $field
     *
     * @return array
     */
    protected static function getRowSpecSections($field)
    {
        return collect(self::getRowSpecDefinitions($field))->map(function ($class, $index) {
            return new $class($index);
        })->values()->toArray();
    }
}
