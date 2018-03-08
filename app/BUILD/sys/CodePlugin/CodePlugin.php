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

class CodePlugin
{
    const CUSTOM_HTML_AGENT_RES = 'agent_res';

    /**
     * In specific cases where the system is looking up classnames
     * plugins can re-write the class name here to change the behaviour.
     *
     * Usually you'd modify the class name by overwriting the symfony DI container config,
     * but some parts of Deskpro don't use that.
     *
     * For example, DefaultDataProcessor will use this when creating data classes.
     * You could use this to overwrite a default data class to change it, or turn
     * it into a no-op.
     *
     * @param string $classname
     *
     * @return string
     */
    public function rewriteClassName($classname)
    {
        return $classname;
    }

    /**
     * Get an array of additional default data classes (classes must all extend AbstractDefaultData).
     *
     * @return array
     */
    public function getDefaultDataClasses()
    {
        return [];
    }

    /**
     * Gives opportunity to overwrite files at the file loading stage (i.e. language SystemLoader).
     *
     * @param string $file The relative lang file being loaded. e.g. default/agent.php
     *
     * @return array
     */
    public function loadLanguageFile($file)
    {
        return [];
    }

    /**
     * Given a file path requested through file.php/dp-asset/{{file}}, return the path to the file to serve.
     * This can be used to serve assets that live outside of the default Deskpro tree.
     *
     * @param string $file
     *
     * @return string|null
     */
    public function getServeAssetPath($file)
    {
        return null;
    }

    /**
     * @param string    $id        The locaiton ID
     * @param Container $container
     *
     * @return null|string
     */
    public function getCustomHtml($id, Container $container)
    {
        return null;
    }
}
