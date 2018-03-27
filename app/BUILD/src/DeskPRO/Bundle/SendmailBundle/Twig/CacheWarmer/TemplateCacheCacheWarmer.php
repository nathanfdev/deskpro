<?php

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
