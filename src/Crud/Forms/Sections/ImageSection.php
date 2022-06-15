<?php

namespace Hellomayaagency\Enso\Mailer\Crud\Forms\Sections;

use App;
use Illuminate\Support\Arr;
use Yadda\Enso\Crud\Forms\Fields\ImageUploadField;
use Yadda\Enso\Crud\Forms\FlexibleContentSection;
use Yadda\Enso\Crud\Handlers\FlexibleRow;
use Yadda\Enso\Media\Contracts\ImageFile as ImageFileContract;

class ImageSection extends FlexibleContentSection
{
    /**
     * Default name for this section
     *
     * @param string
     */
    const DEFAULT_NAME = 'image';

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
