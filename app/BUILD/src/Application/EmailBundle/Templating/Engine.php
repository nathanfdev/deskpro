<?php

/**
 * DeskPRO.
 */

namespace Application\EmailBundle\Templating;

use Application\DeskPRO\App;
use Application\DeskPRO\ResourceScanner\TemplateFiles;
use Application\EmailBundle\Templating\Templates\EmailTemplateCode;
use Symfony\Bundle\FrameworkBundle\Templating\DelegatingEngine as BaseEngine;

class Engine extends BaseEngine
{
    /**
     * @var array()
     */
    protected $_template_files_map;

    /**
     * List of templates who are allowed to have variants.
     *
     * @var array
     */
    protected static $varied_templates = [
        'DeskPRO:emails_agent:ticket-update.html.twig',
        'DeskPRO:emails_agent:new-ticket.html.twig',
        'DeskPRO:emails_agent:new-reply-user.html.twig',
        'DeskPRO:emails_agent:new-reply-agent.html.twig',
        'DeskPRO:emails_agent:blank.html.twig',

        'DeskPRO:emails_user:new-reply-agent.html.twig',
        'DeskPRO:emails_user:new-reply-user.html.twig',
        'DeskPRO:emails_user:new-ticket-agent.html.twig',
        'DeskPRO:emails_user:new-ticket.html.twig',
        'DeskPRO:emails_user:blank.html.twig',
        'DeskPRO:emails_user:ticket-autoclose-warn.html.twig',
    ];

    protected function _initTemplateFilesMap()
    {
        if ($this->_template_files_map !== null) {
            return;
        }

        $tf                        = new TemplateFiles(true);
        $this->_template_files_map = $tf->getTemplateMap();
    }

    /**
     * @return \Application\DeskPRO\ResourceScanner\TemplateFiles
     */
    public function getTemplateFilesMap()
    {
        $this->_initTemplateFilesMap();

        return $this->_template_files_map;
    }

    /**
     * Checks to see if a template is a template shipped with DeskPRO.
     *
     * @param string $name
     *
     * @return bool
     */
    public function isDefaultTemplate($name)
    {
        $this->_initTemplateFilesMap();

        return isset($this->_template_files_map[$name]);
    }

    /**
     * Check if a template is custom.
     *
     * @param string $name
     *
     * @return bool
     */
    public function isCustomTemplate($name)
    {
        $id = App::getDb()->fetchColumn('
            SELECT id
            FROM templates
            WHERE name = ?
            LIMIT 1
        ', [$name]);

        return $id ? true : false;
    }

    /**
     * Get the default source code for a template.
     *
     * @param string $name
     *
     * @return string
     */
    public function getDefaultSource($name)
    {
        $this->_initTemplateFilesMap();
        $path = isset($this->_template_files_map[$name]) ? $this->_template_files_map[$name]['path'] : null;

        if (!$path) {
            return '';
        }

        return file_get_contents($path);
    }

    /**
     * Get the source code for a template. This will return the custom source
     * if its been customised, or the default if not.
     *
     * @param string $name
     *
     * @return string
     */
    public function getSource($name)
    {
        $source = App::getDb()->fetchColumn('
            SELECT template_code
            FROM templates
            WHERE name = ?
            LIMIT 1
        ', [$name]);

        if ($source === false) {
            $source = $this->getDefaultSource($name);
        }

        return $source ?: '';
    }

    /**
     * @param string $name
     *
     * @return array
     */
    public function getSplitSource($name)
    {
        $source = $this->getSource($name);

        return $this->splitSource($source);
    }

    /**
     * @param string $source
     *
     * @return array
     */
    public function splitSource($source)
    {
        $parts = [
            'source' => $source,
        ];

        if (preg_match('#'.EmailTemplateCode::SUBJ_TOKEN_START.'(.*?)'.EmailTemplateCode::SUBJ_TOKEN_END.'#is', $source, $m)) {
            $parts['subject'] = trim($m[1]);
            $parts['body']    = trim(str_replace($m[0], '', $source));
        }

        return $parts;
    }

    /**
     * @return array
     */
    public function getVariedTemplateNames()
    {
        return self::$varied_templates;
    }

    /**
     * @param mixed $name
     *
     * @return bool
     */
    public function exists($name)
    {
        try {
            $GLOBALS['DP_NOLOG_TPL_CACHE_ERR'] = true;
            $ret                               = parent::exists($name) || $this->isCustomTemplate($name);
            $GLOBALS['DP_NOLOG_TPL_CACHE_ERR'] = false;

            return $ret;
        } catch (\Exception $e) {
            $GLOBALS['DP_NOLOG_TPL_CACHE_ERR'] = false;

            return false;
        }
    }
}
