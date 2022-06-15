<?php

namespace Hellomayaagency\Enso\Mailer\Crud\Forms\Sections;

use Yadda\Enso\Crud\Forms\Fields\WysiwygField;
use Yadda\Enso\Crud\Forms\FlexibleContentSection;
use Yadda\Enso\Crud\Handlers\FlexibleRow;

class TextSection extends FlexibleContentSection
{
    /**
     * Default name for this section
     *
     * @param string
     */
    public const DEFAULT_NAME = 'text';

    public function __construct(string $name = 'text')
    {
        parent::__construct($name);

        $this
            ->setLabel('Text')
            ->addFields([
                (new WysiwygField('text')),
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
        return [
            'text' => static::getWysiwygHtml($row->getBlocks(), 'text'),
        ];
    }
}
