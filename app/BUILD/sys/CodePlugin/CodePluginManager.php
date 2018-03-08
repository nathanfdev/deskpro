<?php

/*
 * Deskpro (r) has been developed by Deskpro Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, Deskpro Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that Deskpro is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing Deskpro since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team Deskpro
 */

namespace DpSys\CodePlugin;

use Symfony\Component\DependencyInjection\Container;

class CodePluginManager
{
    /**
     * @var CodePlugins
     */
    private static $inst;

    /**
     * @var CodePlugin[]
     */
    private $plugins = [];

    /**
     * @return CodePlugins
     */
    public static function getManager()
    {
        if (!self::$inst) {
            self::$inst = new self();
        }

        return self::$inst;
    }

    /**
     * @param CodePlugin $plugin
     */
    public function add(CodePlugin $plugin)
    {
        $this->plugins[] = $plugin;
    }

    /**
     * @param string $classname
     *
     * @return string
     */
    public function getRewrittenClassName($classname)
    {
        foreach ($this->plugins as $plugin) {
            $classname = $plugin->rewriteClassName($classname);
        }

        return $classname;
    }

    /**
     * @return array
     */
    public function getExtraDefaultDataClasses()
    {
        $extra = [];

        foreach ($this->plugins as $plugin) {
            $extra = array_merge($extra, $plugin->getDefaultDataClasses());
        }

        return $extra;
    }

    /**
     * @param string $file
     *
     * @return array
     */
    public function loadExtraLangFile($file)
    {
        $extra = [];

        foreach ($this->plugins as $plugin) {
            $extra = array_merge($extra, $plugin->loadLanguageFile($file));
        }

        return $extra;
    }

    /**
     * @param string $file
     *
     * @return null|string
     */
    public function getServeAssetFilePath($file)
    {
        foreach ($this->plugins as $plugin) {
            $f = $plugin->getServeAssetPath($file);
            if ($f) {
                return $f;
            }
        }

        return null;
    }

    /**
     * @param string    $id
     * @param Container $container
     *
     * @return string
     */
    public function getCustomHtml($id, Container $container)
    {
        $html = [];
        foreach ($this->plugins as $plugin) {
            $h = $plugin->getCustomHtml($id, $container);
            if ($h) {
                $html[] = $h;
            }
        }

        return implode("\n", $html);
    }
}
