<?php

namespace Hellomayaagency\Enso\Mailer\Crud\Forms\Sections;

use Yadda\Enso\Crud\Forms\Fields\ImageUploadField;
use Yadda\Enso\Crud\Forms\FlexibleContentSection;
use Yadda\Enso\Crud\Handlers\FlexibleRow;

class ImageSection extends FlexibleContentSection
{
    /**
     * Default name for this section
     *
     * @param string
     */
    public const DEFAULT_NAME = 'image';

    public function __construct(string $name = 'image')
    {
        parent::__construct($name);

        $this
            ->setLabel('Image')
            ->addFields([
                (new ImageUploadField('image')),
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
            'image' => $row->blockContent('image')->first(),
        ];
    }
}
