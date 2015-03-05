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
     * @param  TemplateEntity $entity
     * @return TemplateCustom
     */
    public static function createFromEntity(TemplateEntity $entity)
    {
        $obj = new self($entity->name, $entity);

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
     * Check if the template file exists
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

            if (preg_match('#^DeskPRO:email#', $this->getName())) {
                $this->custom_type = 'email';
            } elseif (strpos($this->getContent(), '<dp:subject') !== false) {
                $this->custom_type = 'email';
            } else {
                $this->custom_type = 'normal';
            }

            return $this->custom_type;
        }
    }
}
