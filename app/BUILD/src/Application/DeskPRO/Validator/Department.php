<?php

/**
 * Orb.
 */

namespace Application\DeskPRO\Validator;

use Application\DeskPRO\App;

class Department extends AbstractPersonContextValidator
{
    /**
     * Allow a non-selection?
     *
     * @var bool
     */
    protected $allow_none = true;

    /**
     * Sometimes a specific value or values might be white-listed
     * even though they are denied by permissions.
     *
     * For example if an agent sets a category a user cant use,
     * then the user modifying the ticket shouldn't trigger an error
     * when it's not changed.
     *
     * @var array
     */
    protected $whitelist = [];

    public function init()
    {
        parent::init();

        $this->allow_none = $this->getOption('allow_none', true);
        $this->whitelist  = (array) $this->getOption('whitelist', []);
    }

    /**
     * Check $value to see if its valid.
     *
     * @return bool
     */
    protected function checkIsValid($value)
    {
        $value = (int) $value;
        if (!$value && !$this->allow_none) {
            $this->addError('none');

            return false;
        }

        if ($value < 1) {
            $this->addError('invalid_int');

            return false;
        }

        $valid_ids = App::getDataService('Department')->getIds();

        if (!in_array($value, $valid_ids)) {
            $this->addError('invalid_id');

            return false;
        }

        //if ($this->hasPerson()) {
        //	$person = $this->getPerson();
        //	$person->loadHelper('PermissionsManager');
        //	if (!$person->getPermsLoader('Departments')->isCategoryAllowed($value)) {
        //		return false;
        //	}
        //}
        return true;
    }
}
