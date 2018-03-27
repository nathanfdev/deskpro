<?php

/**
 * DeskPRO.
 *
 * @category Translate
 */

namespace Application\DeskPRO\Translate\Loader;

/**
 * Loads phrases from filesystem and then database.
 */
class DeskproLoader implements LoaderInterface
{
    /**
     * @var SystemLoader
     */
    protected $sys_loader;

    /**
     * @var DbLoader
     */
    protected $db_loader;

    /**
     * @param SystemLoader $sys_loader
     */
    public function setSystemLoader(SystemLoader $sys_loader)
    {
        $this->sys_loader = $sys_loader;
    }

    /**
     * @param DbLoader $db_loader
     */
    public function setDbLoader(DbLoader $db_loader)
    {
        $this->db_loader = $db_loader;
    }

    /**
     * {@inheritdoc}
     */
    public function load($groups, $language, array $loaded_phrases = null)
    {
        $phrases = [];

        if ($this->sys_loader) {
            $phrases = $this->sys_loader->load($groups, $language);
        }

        if ($this->db_loader) {
            $phrases = array_merge($phrases, $this->db_loader->load($groups, $language, $phrases));
        }

        return $phrases;
    }
}
