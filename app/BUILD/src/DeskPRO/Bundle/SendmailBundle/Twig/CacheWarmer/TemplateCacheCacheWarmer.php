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

namespace DeskPRO\Bundle\SendmailBundle\Twig\CacheWarmer;

use Symfony\Component\Finder\Finder;

class TemplateCacheCacheWarmer extends \Symfony\Bundle\TwigBundle\CacheWarmer\TemplateCacheCacheWarmer
{
    public function warmUp($cacheDir)
    {
        $twig = $this->container->get('twig');
        parent::warmUp($cacheDir);

        // And our extra ones...
        $extra = [
            'TwigBundle:Exception:error.html.twig',
            'TwigBundle:Exception:error403.html.twig',
            'TwigBundle:Exception:error404.html.twig',
            'TwigBundle:Exception:exception.html.twig',
            'TwigBundle:Exception:exception_full.html.twig',
            'TwigBundle::layout.html.twig',
        ];

        // plugin templates
        $template_files = Finder::create()->in(DP_ROOT.'/apps')->name('*.twig');

        foreach ($template_files as $file) {
            $path = $file->getRealPath();
            $path = str_replace('\\', '/', $path);
            $path = str_replace(DP_ROOT.'/apps/', '', $path);

            if (!strpos($path, 'native/Resources/views')) {
                continue;
            }

            $path = str_replace('/native/Resources/views', '', $path);
            $path = str_replace('/', ':', $path);

            // Top-level templates, like AddThis::widget.html.twig
            if (substr_count($path, ':') === 1) {
                $path = str_replace(':', '::', $path);
            }

            $extra[] = $path;
        }

        foreach ($extra as $template_name) {
            try {
                $twig->loadTemplate($template_name);
            } catch (\Twig_Error $e) {
                // problem during compilation, give up
            }
        }
    }
}
