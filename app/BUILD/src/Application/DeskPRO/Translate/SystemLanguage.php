<?php

/**
 * DeskPRO.
 *
 * @category Translate
 */

namespace Application\DeskPRO\Translate;

/**
 * A fake language in the translate class etc.
 */
class SystemLanguage extends \Application\DeskPRO\Entity\Language
{
    /** @var SystemLanguage|null */
    protected static $instance = null;
    public static function getInstance()
    {
        if (self::$instance !== null) {
            return self::$instance;
        }

        self::$instance = new self();

        return self::$instance;
    }

    protected function __construct()
    {
        $this->id            = 0;
        $this->sys_name      = 'default';
        $this->locale        = 'en_US';
        $this->title         = 'English';
        $this->base_filepath = DP_ROOT.'/languages/default';
        $this->has_user      = true;
        $this->has_admin     = true;
        $this->has_agent     = true;
    }
}
