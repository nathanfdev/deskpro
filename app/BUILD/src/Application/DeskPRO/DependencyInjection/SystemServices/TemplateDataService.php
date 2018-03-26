<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;

class TemplateDataService extends BaseRepositoryService
{
    /** @var array|null */
    protected $custom_emails = null;

    public static function create(DeskproContainer $container, array $options = null)
    {
        if (!$options) {
            $options = [];
        }
        $options['entity'] = 'Application\\DeskPRO\\Entity\\Template';

        $em = $container->getEm();
        $o  = new static($em, $options);

        return $o;
    }

    /**
     * Check if a given template is an existing custom email.
     *
     * @param string $name
     *
     * @return bool
     */
    public function customEmailExists($name)
    {
        foreach ($this->getCustomEmails() as $emails) {
            if (in_array($name, $emails)) {
                return true;
            }
        }

        return false;
    }
}
