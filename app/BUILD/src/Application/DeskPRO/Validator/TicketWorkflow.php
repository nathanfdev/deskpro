<?php

/**
 * Orb.
 */

namespace Application\DeskPRO\Validator;

use Application\DeskPRO\App;

class TicketWorkflow extends AbstractPersonContextValidator
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
        if (!$value) {
            if ($this->allow_none) {
                return true;
            }

            $this->addError('none');

            return false;
        }

        if ($value < 1) {
            return false;
        }

        $check_pri = App::getDb()->fetchColumn('SELECT id FROM ticket_workflows WHERE id = ?', [$value]);

        if (!$check_pri) {
            $this->addError('not_exist');

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
