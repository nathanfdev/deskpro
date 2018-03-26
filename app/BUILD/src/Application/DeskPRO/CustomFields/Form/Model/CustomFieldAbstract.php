<?php

namespace Application\DeskPRO\CustomFields\Form\Model;

use Application\DeskPRO\App;
use Application\DeskPRO\CustomFields\Form\StringObject;
use Application\DeskPRO\Entity\CustomDefAbstract;
use DeskPRO\Bundle\AppBundle\Entity\ObjectAlias;
use DeskPRO\Bundle\AppBundle\ObjectAlias\Comparators;

abstract class CustomFieldAbstract
{
    /** @var string */
    public $title;

    /** @var StringObject */
    public $alias = null;

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
    public $display_order            = 0;

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
        $this->_field                   = $field;
        $this->title                    = $field->title;
        $this->description              = $field->description;
        $this->handler_class            = $field->handler_class;
        $this->custom_css_classname     = $field->getOption('custom_css_classname');
        $this->is_agent_field           = $field->is_agent_field;
        $this->is_enabled               = $field->is_enabled;
        $this->agent_validation_resolve = $field->getOption('agent_validation_resolve', false);
        $this->default_value            = $field->default_value;

        if ($field->getOption('required')) {
            $this->required = true;
        }

        if (!$this->_field->id) {
            $this->_is_new = true;
        }

        $this->_em = App::getOrm();

        $this->init();
    }

    /**
     * @return CustomDefAbstract|null
     */
    public function getField()
    {
        return $this->_field;
    }

    protected function init()
    {
    }

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

        $saveAlias = !is_null($this->alias) && ObjectAlias\Aliases::canHaveAlias($this->_field, $this->_em);
        $alias     = null;
        if ($saveAlias && (is_string($this->alias) || $this->alias instanceof  StringObject) && '' !== (string) $this->alias) {
            $aliasBuilder = new ObjectAlias\Builder($this->_em);
            $alias        = ObjectAlias\Aliases::createAlias((string) $this->alias, $this->_field, $aliasBuilder);
        } elseif ($saveAlias && is_array($this->alias)) {
            $alias = array_filter($this->alias, function ($x) {
                return !empty(trim($x));
            });
            $alias = array_map(function ($x) {
                $aliasBuilder = new ObjectAlias\Builder($this->_em);

                return ObjectAlias\Aliases::createAlias((string) $x, $this->_field, $aliasBuilder);
            }, $alias);
        }

        $field->title          = $this->title;
        $field->description    = $this->description ?: '';
        $field->is_enabled     = $this->is_enabled;
        $field->is_agent_field = $this->is_agent_field;
        $field->default_value  = $this->default_value ?: null;

        if ($this->isNewField()) {
            $field->handler_class = $this->handler_class;
        }

        $field->setOption('custom_css_classname', $this->custom_css_classname);
        $field->setOption('agent_validation_resolve', $this->agent_validation_resolve ?: null);

        $field->setOption('agent_validation_type', $this->agent_validation_type);
        $field->setOption('validation_type', $this->validation_type);

        $this->setFieldProperties();

        $this->_em->beginTransaction();
        try {
            $this->_em->persist($field);
            $this->_em->flush();

            if ($saveAlias) {
                if (is_array($alias)) {
                    $this->saveAliasList($alias);
                } else {
                    $this->saveAlias($alias);
                }
            }

            $this->saveAdditional();
            $this->_em->flush();

            $this->_em->commit();
        } catch (\Exception $e) {
            $this->_em->rollback();
            throw $e;
        }
    }

    /**
     * @param array|ObjectAlias\AbstractAlias[] $aliasList
     */
    protected function saveAliasList(array $aliasList)
    {
        if (empty($aliasList)) {
            $this->saveAlias();
        }

        $existingAliasList = $this->_field->getAliases();
        /** @var array|ObjectAlias\AbstractAlias[] $removals */
        $removals = [];
        /** @var array|ObjectAlias\AbstractAlias[] $additions */
        $additions = null;

        if (!$existingAliasList || empty($existingAliasList)) {
            $additions = $aliasList;
        } else {
            $additions = $aliasList;
            foreach ($existingAliasList as $existingAlias) {
                $keep = false;
                foreach ($aliasList as $newAlias) {
                    if (Comparators::equal($newAlias, $existingAlias)) {
                        $keep = $newAlias;
                    }
                }

                if ($keep) {
                    if (($index = array_search($keep, $additions)) !== false) {
                        array_splice($additions, $index, 1);
                    }
                } else {
                    $removals[] = $existingAlias;
                }
            }
        }

        foreach ($removals as $alias) {
            $this->_em->remove($alias);
        }

        foreach ($additions as $alias) {
            $this->_em->persist($alias);
        }
    }

    /**
     * @param ObjectAlias\AbstractAlias $alias
     */
    protected function saveAlias(ObjectAlias\AbstractAlias $alias = null)
    {
        if (is_null($alias)) {
            if (!$this->isNewField()) { // we are removing all existing aliases
                $existingAliasList = $this->_field->getAliases();
                foreach ($existingAliasList as $existingAlias) {
                    $this->_em->remove($existingAlias);
                }
            }

            return;
        }

        $existingAliasList = $this->_field->getAliases();
        if ($existingAliasList) {
            // if the new one is already attached to the field we don't need to do anything
            foreach ($existingAliasList as $existingAlias) {
                if (Comparators::equal($alias, $existingAlias)) {
                    return;
                }
            }

            // replace all other aliases with the new one
            foreach ($existingAliasList as $existingAlias) {
                $this->_em->remove($existingAlias);
            }
        }

        $this->_em->persist($alias);
    }

    protected function setFieldProperties()
    {
    }
    protected function saveAdditional()
    {
    }
}
