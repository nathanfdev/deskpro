<?php

/**
 * DeskPRO.
 *
 * @category Twig
 */

namespace Application\DeskPRO\Twig\Loader;

use Application\DeskPRO\App;

/**
 * This hybrid loader loads templates from the filesystem first, and then from the
 * database second if a style is being used and has templates that override it.
 *
 * NOTE: this has been changed because style entity was deleted
 */
class HybridLoader extends \Symfony\Bundle\TwigBundle\Loader\FilesystemLoader
{
    protected $crashed_custom_templates = [];
    protected $template_info            = [];

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
    }

    public function exists($name)
    {
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
                [$this->template_info[$name]['id']]
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
                'name'
            );
        }
    }

    protected function findTemplate($template, $thow = true)
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

        return parent::findTemplate($template, $thow);
    }
}
