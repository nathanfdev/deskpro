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
 */
namespace DeskPRO\Bundle\PortalBundle\Twig;

use Application\DeskPRO\App;
use DeskPRO\Kernel\KernelErrorHandler;

class Environment extends \Twig_Environment
{
    public function __construct(\Twig_LoaderInterface $loader = null, $options = array())
    {
        parent::__construct($loader, $options);
    }

    public function loadTemplate($name, $index = null)
    {
        $name_str = (string) $name;
        // if not in DB, just load the template
        if (!$this->isCustomTemplate($name_str)) {
            return $this->doLoadTemplate($name, $index);
        } else {
            // it is in the db, so try loading it. if it fails, log exception and try again.
            // under the hood, the loader will not reload from the DB if it crashed.
            try {
                return $this->doLoadTemplate($name, $index);
            } catch (\Exception $e) {
                $errinfo                  = \DeskPRO\Kernel\KernelErrorHandler::getExceptionInfo($e);
                $errinfo['no_send_error'] = true;
                \DeskPRO\Kernel\KernelErrorHandler::logErrorInfo($errinfo);

                $this->markCustomTemplateAsCrashed($name_str);

                return $this->loadTemplate($name, $index);
            }
        }
    }

    private function doLoadTemplate($name, $index = null)
    {
        $cls = $this->getTemplateClass($name, $index);

        if (isset($this->loadedTemplates[$cls])) {
            return $this->loadedTemplates[$cls];
        }
        if (!class_exists($cls, false)) {
            if (false === $cache = $this->getCacheFilename($name)) {
                eval('?>'.$this->compileSource($this->loader->getSource($name), $name));
            } else {
                $evald = false;

                if (strpos($cache, 'dptpl://') === 0) {
                    if ($this->getPortalLoader()->getDbTemplate($name)) {
                        if ($template = $this->getPortalLoader()->getDbTemplate($name)) {
                            try {
                                eval('?>'.$template->template_compiled);
                            } catch (\Exception $e) {
                            }
                            if (class_exists($cls, false)) {
                                $evald = true;
                            }
                        }
                    }
                }

                if (!$evald) {
                    if (!is_file($cache) || ($this->isAutoReload() && !$this->isTemplateFresh($name, filemtime($cache)))) {
                        $fallback = false;
                        $e        = null;
                        try {
                            $this->writeCacheFile($cache, $this->compileSource($this->loader->getSource($name), $name));
                            require_once $cache;
                        } catch (\Exception $e) {
                            $fallback = true;
                        }

                        if ($fallback) {
                            if (!isset($GLOBALS['DP_NOLOG_TPL_CACHE_ERR']) || !$GLOBALS['DP_NOLOG_TPL_CACHE_ERR']) {
                                // Fallback on just evalling the template so everything
                                $prev = null;
                                if ($e) {
                                    $prev = $e;
                                }

                                $name_str = (string) $name;
                                if (preg_match('#^(UserBundle|AgentBundle|DeskPRO|InstallBundle|ReportInterfaceBundle|EmailBundle|CloudAdminBundle|Theme|PortalBundle):#', $name_str)) {
                                    if (defined('DP_BUILD_NUM') && !defined('DP_BUILDING')) {
                                        $e = new \Exception("IMPORTANT: Could not write twig template file for template $name. You should re-download the DeskPRO source files. Contact support@deskpro.com for assistance.", 0, $prev);
                                        KernelErrorHandler::logException($e, false, 'twig_write_failed');
                                    }
                                }
                            }

                            $source = $this->compileSource($this->loader->getSource($name), $name);
                            eval('?>'.$source);
                        }
                    } else {
                        require_once $cache;
                    }
                }
            }
        }

        if (!$this->runtimeInitialized) {
            $this->initRuntime();
        }

        return $this->loadedTemplates[$cls] = new $cls($this);
    }

    /**
     * If theres a custom template with an error, then
     * we'll try and use the default template instead.
     *
     * @param $name
     *
     * @return string
     */
    public function markCustomTemplateAsCrashed($name)
    {
        if ($this->getPortalLoader()->getDbTemplate($name)) {
            $this->getPortalLoader()->markCustomTemplateAsCrashed($name);
        }
    }

    /**
     * Check if a particular template is a custom template.
     *
     * @param $name
     *
     * @return mixed
     */
    public function isCustomTemplate($name)
    {
        if ($this->getPortalLoader()->getDbTemplate($name)) {
            return true;
        }

        return false;
    }

    public function getCacheFilename($name)
    {
        if (!$this->getPortalLoader()->getDbTemplate($name)) {
            return parent::getCacheFilename($name);
        }

        return 'dptpl://load/'.$name;
    }

    /**
     * @return PortalLoader
     */
    protected function getPortalLoader()
    {
        return App::$container->get('portal_loader.twig');
    }

    public function isTemplateFresh($name, $time)
    {
        return $this->loader->isFresh($name, $time);
    }
}
