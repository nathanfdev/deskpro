<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Templating\Templates;

use Application\DeskPRO\Entity\Template as TemplateEntity;

class TemplateCustom extends Template
{
    /**
     * @var \Application\DeskPRO\Entity\Template
     */
    private $entity;

    /**
     * @var \Application\DeskPRO\Templating\Templates\TemplateFile
     */
    private $template_file;

    /**
     * @var string
     */
    private $custom_type;

    /**
     * @param TemplateEntity $entity
     * @param null|string    $customType
     *
     * @return TemplateCustom
     */
    public static function createFromEntity(TemplateEntity $entity, $customType = null)
    {
        $obj = new self($entity->name, $entity);

        if ($customType) {
            $obj->custom_type = $customType;
        }

        return $obj;
    }

    /**
     * @param string         $name
     * @param TemplateEntity $entity
     */
    public function __construct($name, TemplateEntity $entity)
    {
        parent::__construct($name);

        $this->entity = $entity;

        $this->template_file = new TemplateFile($this->entity->name);
    }

    /**
     * @return \Application\DeskPRO\Entity\Template
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * Check if the template file exists.
     *
     * @return bool
     */
    public function exists()
    {
        return $this->entity->id ? true : false;
    }

    /**
     * @return bool
     */
    public function isCustom()
    {
        return true;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->entity->template_code;
    }

    /**
     * @return string
     */
    public function getOriginalName()
    {
        return $this->template_file->getName();
    }

    /**
     * @return string
     */
    public function getOriginalContent()
    {
        return $this->template_file->getContent();
    }

    /**
     * @return EmailTemplateCode|TemplateCode
     */
    public function getOriginalTemplateCode()
    {
        return $this->template_file->getTemplateCode();
    }

    /**
     * @return string
     */
    public function getType()
    {
        if ($this->template_file && $this->template_file->exists()) {
            return $this->template_file->getType();
        } else {
            if ($this->custom_type !== null) {
                return $this->custom_type;
            }

            if (preg_match('#^(DeskPRO|SendmailBundle):email#', $this->getName())) {
                $this->custom_type = 'email';
            } elseif (strpos($this->getContent(), '<dp:subject') !== false) {
                $this->custom_type = 'email';
            } else {
                $this->custom_type = 'normal';
            }

            return $this->custom_type;
        }
    }

    /**
     * @return bool
     */
    public function templateFileExists()
    {
        return $this->template_file->exists();
    }
}
