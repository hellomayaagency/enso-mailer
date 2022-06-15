<?php

namespace Hellomayaagency\Enso\Mailer\Crud\Forms\Sections;

use Illuminate\Support\Arr;
use Yadda\Enso\Crud\Forms\Fields\SelectField;
use Yadda\Enso\Crud\Forms\Fields\TextField;
use Yadda\Enso\Crud\Forms\FlexibleContentSection;
use Yadda\Enso\Crud\Handlers\FlexibleRow;

class CTASection extends FlexibleContentSection
{
    /**
     * Default name for this section
     *
     * @param string
     */
    public const DEFAULT_NAME = 'cta';

    public function __construct(string $name = 'cta')
    {
        parent::__construct($name);

        $this
            ->setLabel('CTA')
            ->addFields([
                TextField::make('text'),
                TextField::make('link'),
                SelectField::make('target')
                    ->setOptions([
                        '_blank' => "New Tab/Window",
                        '_self' => "Current Tab",
                    ]),
            ]);
    }

    /**
     * Unpack Row-specific fields. Should be overriden in Rowspecs that extend
     * this class.
     *
     * @param FlexibleRow $row
     *
     * @return array
     */
    protected static function getRowContent(FlexibleRow $row): array
    {
        $target = Arr::get($row->blockContent('target'), 'id', null);

        return [
            'link' => ! empty($row->blockContent('link')) ? static::sanitizeLink($row->blockContent('link')) : '#',
            'target' => $target ?? '_self',
            'text' => $row->blockContent('text'),
        ];
    }

    /**
     * Sanitizes links so that they always have a protocol (requirement for being
     * present in emails as opposed to on-site).
     *
     * @param string $link
     *
     * @return string
     */
    protected static function sanitizeLink($link): string
    {
        if (! preg_match('#^(http(s)?:)?//#', $link)) {
            return '//' . $link;
        }

        return $link;
    }
}
