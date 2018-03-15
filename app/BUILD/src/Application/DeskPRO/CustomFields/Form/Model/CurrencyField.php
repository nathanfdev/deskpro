<?php

/*
 * Deskpro (r) has been developed by Deskpro Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, Deskpro Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that Deskpro is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing Deskpro since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team Deskpro
 */

namespace Application\DeskPRO\CustomFields\Form\Model;

/**
 * Class CurrencyField.
 */
class CurrencyField extends CustomFieldAbstract
{
    /**
     * @var int
     */
    public $currencyId = false;

    /**
     * {@inheritdoc}
     */
    protected function init()
    {
        $this->required                 = (bool) $this->_field->getOption('required');
        $this->agent_required           = (bool) $this->_field->getOption('agent_required');
        $this->agent_validation_resolve = (bool) $this->_field->getOption('agent_validation_resolve');
        $this->currencyId               = (bool) $this->_field->getOption('currency_id');
    }

    /**
     * {@inheritdoc}
     */
    protected function setFieldProperties()
    {
        $this->_field->setOption('required', $this->required);
        $this->_field->setOption('agent_required', $this->agent_required);
        $this->_field->setOption('agent_validation_resolve', $this->agent_validation_resolve);
        $this->_field->setOption('currency_id', $this->currencyId);
    }
}
