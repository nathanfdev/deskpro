<?php

/**
 * DeskPRO.
 *
 * @category Controller
 */

namespace Application\DeskPRO\ResourceScanner;

class LanguagePacks
{
    /**
     * @var string
     */
    protected $pack_root;

    public function __construct($pack_root = null)
    {
        if ($pack_root === null) {
            $pack_root = DP_ROOT.'/languages';
        }

        $this->pack_root = $pack_root;
    }

    public function getPacks()
    {
        $pack_root = str_replace(DIRECTORY_SEPARATOR, '/', $this->pack_root);

        $finder = new \Symfony\Component\Finder\Finder();
        $finder->files()->name('LangPackage.php')->in([$pack_root]);

        $packs = [];

        foreach ($finder as $file) {
            $class = str_replace(DIRECTORY_SEPARATOR, '/', $file->getPathname());
            $class = str_replace($pack_root, '', $class);
            $class = str_replace('/', '\\', $class);
            $class = str_replace('.php', '', $class);
            $class = 'DeskproLanguages'.$class;

            require_once $file->getPathname();
            $name = $class::getTitle();

            $packs[$class] = $name;
        }

        return $packs;
    }
}
