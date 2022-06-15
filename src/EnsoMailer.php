<?php

namespace Hellomayaagency\Enso\Mailer;

use Exception;
use Hellomayaagency\Enso\Mailer\Contracts\MailParser;
use Hellomayaagency\Enso\Mailer\Contracts\MailSender;
use Hellomayaagency\Enso\Mailer\Mail\Mail;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;
use Yadda\Enso\Media\Contracts\ImageFile;

class EnsoMailer
{
    protected $operand_definitions = [
        'username' => \Hellomayaagency\Enso\Mailer\Handlers\Operands\Username::class,
        'email' => \Hellomayaagency\Enso\Mailer\Handlers\Operands\Email::class,
        'created_at' => \Hellomayaagency\Enso\Mailer\Handlers\Operands\CreatedAt::class,
        'has_roles' => \Hellomayaagency\Enso\Mailer\Handlers\Operands\UserHasRoles::class,
    ];

    protected $operator_definitions = [
        // Select options
        'select_any' => \Hellomayaagency\Enso\Mailer\Handlers\Operators\Select\Any::class,
        'select_not_any' => \Hellomayaagency\Enso\Mailer\Handlers\Operators\Select\NotAny::class,

        // Relationship options
        'relationship_all' => \Hellomayaagency\Enso\Mailer\Handlers\Operators\Select\AllRelations::class,
        'relationship_any' => \Hellomayaagency\Enso\Mailer\Handlers\Operators\Select\AnyRelations::class,
        'relationship_not_all' => \Hellomayaagency\Enso\Mailer\Handlers\Operators\Select\NotAnyRelations::class,
        'relationship_not_any' => \Hellomayaagency\Enso\Mailer\Handlers\Operators\Select\NotAllRelations::class,
        'relationship_at_least' => \Hellomayaagency\Enso\Mailer\Handlers\Operators\Number\AtLeastRelations::class,
        'relationship_equals' => \Hellomayaagency\Enso\Mailer\Handlers\Operators\Number\EqualsRelations::class,
        'relationship_not_equals' => \Hellomayaagency\Enso\Mailer\Handlers\Operators\Number\NotEqualsRelations::class,
        'relationship_less_than' => \Hellomayaagency\Enso\Mailer\Handlers\Operators\Number\LessThanRelations::class,
        'relationship_more_than' => \Hellomayaagency\Enso\Mailer\Handlers\Operators\Number\MoreThanRelations::class,
        'relationship_no_more_than' => \Hellomayaagency\Enso\Mailer\Handlers\Operators\Number\NoMoreThanRelations::class,

        // Number options
        'number_at_least' => \Hellomayaagency\Enso\Mailer\Handlers\Operators\Number\AtLeast::class,
        'number_equals' => \Hellomayaagency\Enso\Mailer\Handlers\Operators\Number\Equals::class,
        'number_not_equals' => \Hellomayaagency\Enso\Mailer\Handlers\Operators\Number\NotEquals::class,
        'number_less_than' => \Hellomayaagency\Enso\Mailer\Handlers\Operators\Number\LessThan::class,
        'number_more_than' => \Hellomayaagency\Enso\Mailer\Handlers\Operators\Number\MoreThan::class,
        'number_no_more_than' => \Hellomayaagency\Enso\Mailer\Handlers\Operators\Number\NoMoreThan::class,

        // String options
        'string_matches' => \Hellomayaagency\Enso\Mailer\Handlers\Operators\String\Matches::class,
        'string_begins_with' => \Hellomayaagency\Enso\Mailer\Handlers\Operators\String\BeginsWith::class,
        'string_partial_match' => \Hellomayaagency\Enso\Mailer\Handlers\Operators\String\PartialMatch::class,
        'string_ends_with' => \Hellomayaagency\Enso\Mailer\Handlers\Operators\String\EndsWith::class,
        'string_not_begins_with' => \Hellomayaagency\Enso\Mailer\Handlers\Operators\String\NotBeginsWith::class,
        'string_not_partial_match' => \Hellomayaagency\Enso\Mailer\Handlers\Operators\String\NotPartialMatch::class,
        'string_not_ends_with' => \Hellomayaagency\Enso\Mailer\Handlers\Operators\String\NotEndsWith::class,
        'string_not_matches' => \Hellomayaagency\Enso\Mailer\Handlers\Operators\String\NotMatches::class,

        // Date options
        'date_greater_than' => \Hellomayaagency\Enso\Mailer\Handlers\Operators\Date\After::class,
        'date_on_or_greater_than' => \Hellomayaagency\Enso\Mailer\Handlers\Operators\Date\OnOrAfter::class,
        'date_equals' => \Hellomayaagency\Enso\Mailer\Handlers\Operators\Date\OnDays::class,
        'date_on_or_less_than' => \Hellomayaagency\Enso\Mailer\Handlers\Operators\Date\OnOrBefore::class,
        'date_less_than' => \Hellomayaagency\Enso\Mailer\Handlers\Operators\Date\Before::class,
    ];

    /**
     * Gets an json string that represents all of the available operand and
     * operator options, as well as which operands can use which operators.
     *
     * @return string
     */
    public function getFormDataJson()
    {
        $operands = collect($this->getOperandDefinitions())->map(function ($operand) {
            return (new $operand)->getJsonData();
        })->toArray();

        $operators = collect($this->getOperatorDefinitions())->map(function ($operator) {
            return (new $operator)->getJsonData();
        })->toArray();

        return collect([
            'operands' => $operands,
            'operators' => $operators,
        ])->toJson();
    }

    /**
     * Gets the names of all current Operand definitions.
     *
     * @return array
     */
    public function getOperandNames()
    {
        return array_keys($this->getOperandDefinitions());
    }

    /**
     * Gets all of the Operand Definitions available on the Mailer.
     *
     * @return array
     */
    public function getOperandDefinitions()
    {
        return $this->operand_definitions;
    }

    /**
     * Gets the full class for a specific operand, by name
     *
     * @param string $operand_key
     *
     * @return string
     */
    public function getOperandClass($operand_key)
    {
        try {
            return $this->getOperandDefinitions()[$operand_key];
        } catch (Exception $e) {
            Log::error('Operand `' . $operand_key . '` missing or invalid. ' . $e->getMessage());
            throw new Exception('Operand `' . $operand_key . '` missing or invalid.');
        }
    }

    /**
     * Gets the Object represendation of an operand, operand and value
     * set. These are used to apply modifiers to a base query.
     *
     * @param string $operand
     *
     * @return Yadda\Enso\Mailer\Models\BaseQueryModifier - An implementation of this abstract class
     */
    public function getOperandObject($operand)
    {
        try {
            $class = $this->getOperandClass($operand);
            return new $class;
        } catch (Exception $e) {
            Log::error('Operand class for `' . $operand . '` missing or invalid. ' . $e->getMessage());
            throw new Exception('Operand class for `' . $operand . '` missing or invalid.');
        }
    }

    /**
     * Gets the names of the currently available operators.
     *
     * @return array
     */
    public function getOperatorNames()
    {
        return array_keys($this->getOperatorDefinitions());
    }

    /**
     * Gets all of the Operator Definitions available on the Mailer.
     *
     * @return array
     */
    public function getOperatorDefinitions()
    {
        return $this->operator_definitions;
    }

    /**
     * Gets the full class for a specific operator, by name
     *
     * @param string $operator_key
     *
     * @return string
     */
    public function getOperatorClass($operator_key)
    {
        try {
            return $this->getOperatorDefinitions()[$operator_key];
        } catch (Exception $e) {
            Log::error('Operator `' . $operator_key . '` missing or invalid. ' . $e->getMessage());
            throw new Exception('Operator `' . $operator_key . '` missing or invalid.');
        }
    }

    /**
     * Gets the Object represendation of an operator, operator and value
     * set. These are used to apply modifiers to a base query.
     *
     * @param string $operator
     *
     * @return Yadda\Enso\Mailer\Models\BaseQueryModifier - An implementation of this abstract class
     */
    public function getOperatorObject($operator)
    {
        try {
            $class = $this->getOperatorClass($operator);
            return new $class;
        } catch (Exception $e) {
            Log::error('Operator class for `' . $operator . '` missing or invalid. ' . $e->getMessage());
            throw new Exception('Operator class for `' . $operator . '` missing or invalid.');
        }
    }

    /**
     * Updates some or all of a definition for a single operand, by name. You do not
     * need to specify a full data set, just the items that you want to update.
     *
     * @param string $name
     * @param array $modifier
     *
     * @return self
     */
    protected function updateOperandDefinition($name, $modifier)
    {
        $this->operand_definitions[$name] = $modifier;

        return $this;
    }

    /**
     * More fluent name for a generic function.
     *
     * @param string $name
     * @param array  $modifier
     *
     * @return self
     */
    public function addOperandDefinition($name, $modifier)
    {
        $this->updateOperandDefinition($name, $modifier);

        return $this;
    }

    /**
     * Update an array of operands. $modifiers should be an array of
     * name => modifier pairs.
     *
     * @param array $modifiers
     *
     * @return self
     */
    public function updateOperandDefinitions($modifiers)
    {
        foreach ($modifiers as $name => $modifier) {
            $this->updateOperandDefinition($name, $modifier);
        }

        return $this;
    }

    /**
     * More fluent name for a generic function.
     *
     * @param array $modifier
     *
     * @return self
     */
    public function addOperandDefinitions($modifiers)
    {
        $this->updateOperandDefinitions($modifiers);

        return $this;
    }

    /**
     * Updates some or all of a definition for a single operand, by name. You do not
     * need to specify a full data set, just the items that you want to update.
     *
     * @param string $name
     * @param array $modifier
     *
     * @return self
     */
    public function updateOperatorDefinition($name, $modifier)
    {
        $this->operator_definitions[$name] = $modifier;

        return $this;
    }

    /**
     * More fluent name for a generic function.
     *
     * @param string $name
     * @param array  $modifier
     *
     * @return self
     */
    public function addOperatorDefinition($name, $modifier)
    {
        $this->updateOperatorDefinition($name, $modifier);

        return $this;
    }

    /**
     * Update an array of operands. $modifiers should be an array of
     * name => modifier pairs.
     *
     * @param array $modifiers
     *
     * @return self
     */
    public function updateOperatorDefinitions($modifiers)
    {
        foreach ($modifiers as $name => $modifier) {
            $this->updateOperatorDefinition($name, $modifier);
        }

        return $this;
    }

    /**
     * More fluent name for a generic function.
     *
     * @param array $modifier
     *
     * @return self
     */
    public function addOperatorDefinitions($modifiers)
    {
        $this->updateOperatorDefinitions($modifiers);

        return $this;
    }

    /**
     * Validates whether array data from a query builder is valid.
     *
     * @param array $condition
     *
     * @return boolean
     */
    public function conditionIsValid($condition)
    {
        $type = $condition['type'] ?? null;
        if (
            empty($type) ||
            !in_array(strtoupper($type), ['AND', 'OR'])
        ) {
            return false;
        }

        $component = strtolower($condition['component'] ?? '');
        if (
            empty($component) ||
            !in_array($component, ['query-group', 'query-condition']) ||
            !call_user_func([$this, 'validate' . Str::studly($component)], $condition)
        ) {
            return false;
        }

        $conditions = $condition['conditions'] ?? [];
        foreach ($conditions as $condition) {
            if (!$this->conditionIsValid($condition)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Function to validate QueryGroup items.
     *
     * At present, any query group is valid. If a user enters a query group with
     * no conditions, then it just won't get saved as anything.
     *
     * The only things which could be invalid will already have been checked
     * before reaching this function (type and component)
     *
     * @param array $condition.
     *
     * @return boolean
     */
    protected function validateQueryGroup($condition)
    {
        return true;
    }

    /**
     * Validates that a give 'condition' type query item is valid.
     *
     * Note that for now, we're excluding empty items, from validation.
     * Given that the form itself offers validity notifications by way of input
     * border colors. It seems more user-friendly to just cut out out empty
     * items on saving than to be throwing errors back at them.
     *
     * @param array $condition
     *
     * @return boolean
     */
    protected function validateQueryCondition($condition)
    {
        $operand = $condition['operand'] ?? '';
        if (
            !empty($operand) &&
            !in_array($operand, $this->getOperandNames())
        ) {
            return false;
        }

        $operator = $condition['operator'] ?? '';
        if (
            !empty($operator) && (!in_array($operator, $this->getOperatorNames()) ||
                !$this->getOperandObject($operand)->isValidOperator($operator))
        ) {
            return false;
        }

        $data = $condition['data'] ?? [];
        if (!is_array($data)) {
            return false;
        }

        // @todo - Look into how we should best to data validation, given that it could be
        //         theoretically anything within the array.

        return true;
    }

    /**
     * Gets an instance of the Mail Sender that should be used to send campaigns. If a driver
     * if provided, get the sender for that specific driver.
     *
     * @param $driver
     *
     * @return MailSender
     */
    public function getSender($driver = null)
    {
        if ($driver) {
            $sender_class = config('enso.mailer.drivers.' . $driver . '.sender');

            $sender = new $sender_class;
        } else {
            $sender = App::make(MailSender::class);
        }

        return $sender;
    }

    /**
     * Gets an instance of the Mail Parser that should be used to send campaigns. If a driver
     * if provided, get the sender for that specific driver.
     *
     * @param $driver
     *
     * @return MailParser
     */
    public function getParser($driver = null)
    {
        if ($driver) {
            $parser_class = config('enso.mailer.drivers.' . $driver . '.parser');

            $parser = new $parser_class;
        } else {
            $parser = App::make(MailParser::class);
        }

        return $parser;
    }

    /**
     * Gets the class of the Mailable, or the default Mailable.
     *
     * @return string
     */
    public function getMailableClass()
    {
        return config('enso.mailer.mailable', Mail::class);
    }

    /**
     * Checks whether a Company Logo has been saved for email use
     *
     * @return boolean
     */
    public function hasMailHeaderImage()
    {
        return !empty($this->getMailHeaderImage());
    }

    /**
     * Gets either a url or path for the Main Header Image, if it has been specified.
     *
     * @param string $type
     *
     * @return string
     */
    public function getMailHeaderImage($type = 'url')
    {
        $settings = app('ensosettings');
        $header_setting = $settings->get('mailer_header_image');
        if (count($header_setting)) {
            $image_id = Arr::get(Arr::first($header_setting), 'id');
            $image_class = app(ImageFile::class);
            $image = $image_class::find($image_id);

            if ($image) {
                switch ($type) {
                    case 'path':
                        return $image->getPath();
                    case 'url':
                    default:
                        return $image->getUrl();
                }
            }
        }

        return;
    }

    /**
     * Checks whether a Company Logo has been saved for email use
     *
     * @return boolean
     */
    public function hasMailCompanyLogo()
    {
        return !empty($this->getMailCompanyLogo());
    }

    /**
     * Get either a URL or a path to the Company Logo, if it has been specified.
     *
     * @param string $type
     *
     * @return string
     */
    public function getMailCompanyLogo($type = 'url')
    {
        $settings = app('ensosettings');
        $header_setting = $settings->get('mailer_company_logo');

        if ($header_setting) {
            $image_id = Arr::get(Arr::first($header_setting), 'id');
            $image_class = app(ImageFile::class);
            $image = $image_class::find($image_id);

            if ($image) {
                switch ($type) {
                    case 'path':
                        return $image->getPath();
                    case 'url':
                    default:
                        return $image->getUrl();
                }
            }
        }

        return null;
    }

    /**
     * Checks whether there is a fallback title.
     *
     * @return boolean
     */
    public function hasMailCompanyTitle()
    {
        return !empty($this->getMailCompanyTitle());
    }

    /**
     * Gets the MailCompanyTitle for when there is no logo
     *
     * @return string
     */
    public function getMailCompanyTitle()
    {
        $settings = app('ensosettings');
        $mail_company_name = $settings->get('mailer_company_name');

        if (empty($mail_company_name)) {
            $mail_company_name = $settings->get('site-name');
        }

        return $mail_company_name;
    }

    /**
     * Check to see whether there is a social icons list available to use
     *
     * @return boolean
     */
    public function hasSocialTemplate()
    {
        return View::exists('enso-crud::mailer_email.partials.social-icon-list');
    }

    /**
     * Check to see whether the top of the footer needs displaying
     *
     * @return boolean
     */
    public function displayFooterTop()
    {
        return $this->hasMailCompanyLogo() || $this->hasSocialTemplate();
    }

    /**
     * Checks to see whether there is a copyright year set up
     *
     * @return boolean
     */
    public function hasMailCompanyCopyright()
    {
        return !empty($this->getMailCompanyCopyright());
    }

    /**
     * Gets the copyright year.
     *
     * @return string
     */
    public function getMailCompanyCopyright()
    {
        $settings = app('ensosettings');
        $copyright_setting = $settings->get('mailer_company_copyright');

        return $copyright_setting;
    }

    /**
     * Gets the company name for the Footer. This could need to be different
     * from the company name that is used as a fallback for the logo image.
     *
     * @return string
     */
    public function getMailCompanyFooterName()
    {
        $settings = app('ensosettings');
        $company_name = $settings->get('mailer_company_footer_name');

        if (empty($company_name)) {
            $company_name = $settings->get('site-name');
        }

        return $company_name;
    }

    /**
     * Check to see if there is a set company email for the mailer (or an administrator fallback set)
     *
     * @return boolean
     */
    public function hasMailCompanyEmail()
    {
        return !empty($this->getMailCompanyEmail());
    }

    /**
     * Gets the Company email address
     *
     * @return string
     */
    public function getMailCompanyEmail()
    {
        $settings = app('ensosettings');
        $company_email = $settings->get('mailer_company_email');

        if (empty($company_email)) {
            $company_email = $settings->get('administrator-email');
        }

        return $company_email;
    }

    /**
     * Gets a link for a client to update their Mailer preferences
     *
     * @placeholder
     *
     * @return string
     */
    public function getMailUpdateProfileLink()
    {
        return '#';
    }

    /**
     * Gets a link for a client to unsubscribe from the Mailer
     *
     * @placeholder
     *
     * @return string
     */
    public function getMailUnsubscribeLink()
    {
        return '#';
    }
}
