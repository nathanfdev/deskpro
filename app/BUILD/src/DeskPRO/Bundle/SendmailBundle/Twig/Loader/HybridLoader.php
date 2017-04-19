<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\SendmailBundle\Twig\Loader;

use Application\DeskPRO\App;
use DeskPRO\Bundle\SendmailBundle\Twig\PreProcessor\EmailPreProcessor;
use Symfony\Bundle\TwigBundle\Loader\FilesystemLoader;

class HybridLoader extends FilesystemLoader
{
    protected $crashedCustomTemplates = [];
    protected $templateInfo           = [];

    public function dbHasTemplate($name)
    {
        if (isset($this->crashedCustomTemplates[(string) $name])) {
            return false;
        }

        $this->_initTemplates();
        if (isset($this->templateInfo[(string) $name])) {
            return true;
        }

        return false;
    }

    public function isFresh($name, $time)
    {
        $this->_initTemplates();

        $strName = (string) $name;

        // DB templates are always "fresh" because theyre compiled
        // as soon as they're saved
        if (!isset($this->crashedCustomTemplates[$strName]) && isset($this->templateInfo[$strName])) {
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

        $strName = (string) $name;
        if (!isset($this->crashedCustomTemplates[$strName]) && isset($this->templateInfo[$strName])) {
            return App::getDb()->fetchColumn(
                '
                SELECT template_code
                FROM templates
                WHERE id = ?
            ',
                [$this->templateInfo[$name]['id']]
            );
        }

        $source = file_get_contents($this->findTemplate($name));

        if (strpos($name, 'DeskPRO:emails_') !== false || strpos($name, 'SendmailBundle:') !== false) {
            $proc   = new EmailPreProcessor();
            $source = $proc->process($source, $strName);
        }

        return $source;
    }

    protected function _initTemplates()
    {
        if (!defined('DP_BUILDING') && empty($this->templateInfo)) {
            $this->templateInfo = App::getDb()->fetchAllKeyed(
                '
                SELECT id, name, UNIX_TIMESTAMP(date_updated) AS date_updated
                FROM templates
                ',
                [],
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
                        $pathName = preg_replace('#^.*?:(.*?)$#', '$2', $logicalName);
                        $pathName = str_replace(':', '/', $pathName);
                        $path     = DP_ROOT.'/apps/'.$package->native_name.'/native/Resources/views/'.$pathName;

                        return $path;
                    }
                } catch (\Exception $e) {
                }
            }
        }

        return parent::findTemplate($template, $throw);
    }
}
