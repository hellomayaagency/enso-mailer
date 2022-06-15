<?php

namespace Hellomayaagency\Enso\Mailer\Contracts;

interface CampaignCrud
{
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
     * @param array $content
     *
     * @return \Illuminate\Support\Collection
     */
    public static function getRowSpecContent($field, $content);
}
