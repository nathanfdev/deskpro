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

namespace Application\ImportBundle\Entity;

use Symfony\Component\Validator\Constraints;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * Class CustomDef
 * @package Application\ImportBundle\Entity
 */
final class CustomDef extends AbstractEntity
{
    /**
     * @var int
     */
    private $parent_id;

    /**
     * @var string
     */
    private $sys_name;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $handler_class;

    /**
     * @var bool
     */
    private $is_enabled;

    /**
     * @var array
     */
    private $options = array();

    /**
     * {@inheritdoc}
     */
    public function getType()
    {
        return self::TYPE_CUSTOM_DEF;
    }

    /**
     * @return int
     */
    public function getParentId()
    {
        return $this->parent_id;
    }

    /**
     * @param int $parent_id
     * @return $this
     */
    public function setParentId($parent_id)
    {
        $this->parent_id = $parent_id;
        return $this;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle($title)
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * @return string
     */
    public function getHandlerClass()
    {
        return $this->handler_class;
    }

    /**
     * @param string $handler_class
     * @return $this
     */
    public function setHandlerClass($handler_class)
    {
        $this->handler_class = $handler_class;
        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->is_enabled;
    }

    /**
     * @param bool $is_enabled
     * @return $this
     */
    public function setAsEnabled($is_enabled)
    {
        $this->is_enabled = (bool)$is_enabled;
        return $this;
    }

    /**
     * @return string
     */
    public function getSysName()
    {
        return $this->sys_name;
    }

    /**
     * @param string $sys_name
     * @return $this
     */
    public function setSysName($sys_name)
    {
        $this->sys_name = $sys_name;
        return $this;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     * @return $this
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function toArray()
    {
        return array(
            'oid'           => $this->oid,
            'sys_name'      => $this->sys_name,
            'parent_id'     => $this->parent_id,
            'title'         => $this->title,
            'description'   => $this->description,
            'handler_class' => $this->handler_class,
            'is_enabled'    => $this->is_enabled,
            'options'       => $this->options,
        );
    }

    /**
     * {@inheritdoc}
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        AbstractEntity::loadValidatorMetadata($metadata);

        $metadata
            ->addPropertyConstraint('sys_name', new Constraints\NotBlank())
            ->addPropertyConstraint('title', new Constraints\NotBlank())
            ->addPropertyConstraint('description', new Constraints\NotBlank())
            ->addPropertyConstraint('handler_class', new Constraints\NotBlank())
        ;
    }
}
