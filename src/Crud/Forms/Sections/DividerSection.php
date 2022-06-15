<?php

namespace Hellomayaagency\Enso\Mailer\Crud\Forms\Sections;

use Yadda\Enso\Crud\Forms\Fields\TextField;
use Yadda\Enso\Crud\Forms\FlexibleContentSection;

class DividerSection extends FlexibleContentSection
{
    /**
     * Default name for this section
     *
     * @param string
     */
    public const DEFAULT_NAME = 'divider';

    public function __construct(string $name = 'divider')
    {
        parent::__construct($name);

        $this
            ->setLabel('Divider')
            ->addFields([
                (new TextField('divider'))
                    ->setDisabled(true)
                    ->setHelpText('You do not need to add any data to this row'),
            ]);
    }
}
