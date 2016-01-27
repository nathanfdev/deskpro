<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

/**
 * DeskPRO.
 *
 * @category Twig
 */

namespace Application\EmailBundle\Twig\Loader;

use Application\DeskPRO\App;
use Symfony\Bundle\TwigBundle\Loader\FilesystemLoader;

/**
 * This hybrid loader loads templates from the filesystem first, and then from the
 * database second if a style is being used and has templates that override it.
 */
class HybridLoader extends FilesystemLoader
{
    protected $crashed_custom_templates = array();
    protected $template_info            = array();

    public function markCustomTemplateAsCrashed($name)
    {
        $this->crashed_custom_templates[$name] = true;
    }

    public function dbHasTemplate($name)
    {
        if (isset($this->crashed_custom_templates[(string) $name])) {
            return false;
        }

        $this->_initTemplates();
        if (isset($this->template_info[(string) $name])) {
            return true;
        }

        return false;
    }

    public function isFresh($name, $time)
    {
        $this->_initTemplates();

        $str_name = (string) $name;

        // DB templates are always "fresh" because theyre compiled
        // as soon as they're saved
        if (!isset($this->crashed_custom_templates[$str_name]) && isset($this->template_info[$str_name])) {
            return true;
        }

        return parent::isFresh($name, $time);
    }

    public function getCacheKey($name)
    {
        return md5((string) $name);
    }

    public function getSource($name)
    {
        $this->_initTemplates();

        $str_name = (string) $name;
        if (!isset($this->crashed_custom_templates[$str_name]) && isset($this->template_info[$str_name])) {
            return App::getDb()->fetchColumn(
                '
                SELECT template_code
                FROM templates
                WHERE id = ?
            ',
                array($this->template_info[$name]['id'])
            );
        }

        $source = file_get_contents($this->findTemplate($name));

        if (strpos($name, 'DeskPRO:emails_') !== false || strpos($name, 'EmailBundle:') !== false) {
            $proc   = new \Application\DeskPRO\Twig\PreProcessor\EmailPreProcessor();
            $source = $proc->process($source, $str_name);
        }

        return $source;
    }

    protected function _initTemplates()
    {
        if (!defined('DP_BUILDING') && empty($this->template_info)) {
            $this->template_info = App::getDb()->fetchAllKeyed(
            '
            SELECT id, name, UNIX_TIMESTAMP(date_updated) AS date_updated
            FROM templates
            ',
            array(),
            'name'
            );
        }
    }

    protected function findTemplate($template, $throw = true)
    {
        $this->_initTemplates();

        $logicalName = (string) $template;

        if (strpos($logicalName, 'Apps:') === 0) {
            if (class_exists('Application\\DeskPRO\\App', false)) {
                $logicalName = preg_replace('#^Apps:#', '', $logicalName);

                try {
                    $manager = App::getContainer()->getAppManager();
                    $package = null;
                    foreach ($manager->getAllPackages() as $p) {
                        if (!$p->native_name) {
                            continue;
                        }
                        if (preg_match('#^'.preg_quote($p->native_name).':#', $logicalName)) {
                            $package = $p;
                            break;
                        }
                    }

                    if ($package) {
                        $path_name = preg_replace('#^.*?:(.*?)$#', '$2', $logicalName);
                        $path_name = str_replace(':', '/', $path_name);
                        $path      = DP_ROOT.'/apps/'.$package->native_name.'/native/Resources/views/'.$path_name;

                        return $path;
                    }
                } catch (\Exception $e) {
                }
            }
        }

        return parent::findTemplate($template, $throw);
    }
}
