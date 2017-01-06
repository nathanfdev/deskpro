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

/**
 * DeskPRO.
 */

namespace DeskPRO\Bundle\DevBundle\Template;

/**
 * Should convert uses of template-map.php into a service + cache warmer.
 *
 * @deprecated
 */
class TemplatesScanner
{
    /**
     * DP_ROOT relative path.
     */
    const DUMP_PATH = '/sys/config/template-map.php';

    /**
     * Collect templates data and dump it to cache file.
     */
    public static function dump()
    {
        $tpl_info = self::scan();

        $php = ["<?php return array(\n"];

        foreach ($tpl_info as $k => $info) {
            $php[] = "'$k' => array('path' => {$info['path']}, 'last_updated' => {$info['last_updated']}),\n";
        }

        $php[] = ');';
        $php[] = "\n";

        file_put_contents(DP_APP_DIR.self::DUMP_PATH, implode('', $php));

        self::cli("\n");
    }

    /**
     * Collect templates data.
     *
     * @return array
     */
    private static function scan()
    {
        $paths = [
            'AdminInterfaceBundle'   => DP_APP_DIR.'/src/Application/AdminInterfaceBundle/Resources/views',
            'AgentBundle'            => DP_APP_DIR.'/src/Application/AgentBundle/Resources/views',
            'DeskPRO'                => DP_APP_DIR.'/src/Application/DeskPRO/Resources/views',
            'ReportsInterfaceBundle' => DP_APP_DIR.'/src/Application/ReportsInterfaceBundle/Resources/views',
            'PortalBaseTheme'        => DP_APP_DIR.'/src/DeskPRO/Bundle/PortalBundle/Themes/Base/Resources/views',
            'PortalSidebarTheme'     => DP_APP_DIR.'/src/DeskPRO/Bundle/PortalBundle/Themes/Sidebar/Resources/views',
            'PortalStandardTheme'    => DP_APP_DIR.'/src/DeskPRO/Bundle/PortalBundle/Themes/Standard/Resources/views',
        ];

        $tpl_info = [];

        $bogus = true;
        if (array_key_exists('argv', $_SERVER) && in_array('--real-time', $_SERVER['argv'])) {
            $bogus = false;
        }

        foreach ($paths as $bundle => $dir) {
            $finder = new \Symfony\Component\Finder\Finder();
            $finder->files()->name('*.twig')->in($dir);

            foreach ($finder as $file) {
                /* @var \Symfony\Component\Finder\SplFileinfo $file */

                $filepath = $file->getRealPath();

                $tplname = str_replace($dir.'/', ':', $filepath);
                $tplname = str_replace('/', ':', $tplname);
                if (substr_count($tplname, ':') < 2) {
                    $tplname = ':'.$tplname; // for layouts that are in top dir, MyBundle::layout
                }
                $tplname = $bundle.$tplname;

                if (!$bogus) {
                    exec("git log --date=short -s -1 -- {$filepath}", $out);
                    $res = implode("\n", $out);

                    preg_match('#^Date:\s*([0-9]{4}\-[0-9]{2}\-[0-9]{2})#m', $res, $m);
                    $time = strtotime($m[1]);
                } else {
                    $time = time();
                }

                $path = $file->getRealPath();
                if (strpos($path, DP_ROOT) === 0) {
                    $path = str_replace(DP_ROOT, '', $file->getRealPath());
                    $path = "DP_ROOT.'$path'";
                } else {
                    $path = str_replace(DP_WEB_ROOT, '', $file->getRealPath());
                    $path = "DP_ROOT.'/..$path'";
                }

                $tpl_info[$tplname] = [
                    'path'         => $path,
                    'last_updated' => $time,
                ];

                self::cli('.');
            }
        }

        return $tpl_info;
    }

    /**
     * Print text if in CLI.
     *
     * @param string $text
     */
    private static function cli($text)
    {
        if (php_sapi_name() === 'cli') {
            echo $text;
        }
    }
}
