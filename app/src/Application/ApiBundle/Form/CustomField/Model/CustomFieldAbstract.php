<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage AdminBundle
 */

namespace Application\ApiBundle\Form\CustomField\Model;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity\CustomDefAbstract;

abstract class CustomFieldAbstract
{
    /** @var string */
    public $title;
    /** @var string */
    public $description = '';
    /** @var string */
    public $handler_class;
    /** @var string */
    public $default_value;

    /** @var bool */
    public $required = false;
    /** @var bool */
    public $agent_required = false;

    /** @var null|string */
    public $custom_css_classname = '';
    /** @var string */
    public $custom_css = '';
    /** @var string */
    public $validation_type = '';
    /** @var string */
    public $agent_validation_type = '';
    /** @var bool */
    public $is_enabled = false;
    /** @var bool */
    public $is_agent_field = false;
    /** @var bool */
    public $agent_validation_resolve = false;
    public $display_order = 0;

    /** @var \Application\DeskPRO\Entity\CustomDefAbstract|null */
    protected $_field = null;
    /** @var bool */
    protected $_is_new = false;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $_em;

    public function __construct(CustomDefAbstract $field)
    {
        $this->_field = $field;
        $this->title = $field->title;
        $this->description = $field->description;
        $this->handler_class = $field->handler_class;
        $this->custom_css_classname = $field->getOption('custom_css_classname');
        $this->is_agent_field = $field->is_agent_field;
        $this->is_enabled = $field->is_enabled;
        $this->agent_validation_resolve = $field->getOption('agent_validation_resolve', false);
        $this->default_value = $field->default_value;

        if ($field->getOption('required')) {
            $this->required = true;
        }

        if (!$this->_field->id) {
            $this->_is_new = true;
        }

        $this->_em = App::getOrm();

        $this->init();
    }

    protected function init() {}

    public function isNewField()
    {
        return $this->_is_new;
    }

    public function save()
    {
        $field = $this->_field;

        if (!$this->title) {
            $this->title = 'Untitled';
        }

        $field->title = $this->title;
        $field->description = $this->description ?: '';
        $field->is_enabled = $this->is_enabled;
        $field->is_agent_field = $this->is_agent_field;
        $field->default_value = $this->default_value ?: null;

        if ($this->isNewField()) {
            $field->handler_class = $this->handler_class;
        }

        $field->setOption('custom_css_classname', $this->custom_css_classname);
        $field->setOption('agent_validation_resolve', $this->agent_validation_resolve ?: null);

        $this->setFieldProperties();

        $this->_em->beginTransaction();
        try {
            $this->_em->persist($field);
            $this->_em->flush();

            $this->saveAdditional();
            $this->_em->flush();

            $this->_em->commit();
        } catch (\Exception $e) {
            $this->_em->rollback();
            throw $e;
        }
    }

    protected function setFieldProperties() {}
    protected function saveAdditional() {}
}
