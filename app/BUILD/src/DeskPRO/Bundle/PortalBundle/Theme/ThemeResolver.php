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

namespace DeskPRO\Bundle\PortalBundle\Theme;

use Application\DeskPRO\Entity\Template;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSet;
use Doctrine\ORM\EntityManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ThemeResolver
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;

    /**
     * @var ThemeRepository
     */
    private $theme_repo;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var TagProcessor
     */
    private $tag_processor;

    /**
     * @var array
     */
    private $themeTemplateMap;

    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(
        ContainerInterface $container,
        ThemeRepository $theme_repo,
        TagProcessor $tag_processor,
        EntityManager $em,
        LoggerInterface $logger
    ) {
        $this->container        = $container;
        $this->theme_repo       = $theme_repo;
        $this->themeTemplateMap = null;
        $this->logger           = $logger;
        $this->tag_processor    = $tag_processor;
        $this->em               = $em;
    }

    /**
     * @param ThemeInterface $theme
     * @param                $input_controller
     *
     * @throws \RuntimeException
     *
     * @return null|string
     */
    public function controller(ThemeInterface $theme, $input_controller)
    {
        if (
            is_string($input_controller)
            && 'Theme:' === substr($input_controller, 0, 6)
            && 3 === count($parts = explode(':', $input_controller))
        ) {
            $controller = $parts[1];
            $action     = $parts[2];

            $try = $theme->getNamespace().'\\Controller\\'.$controller.'Controller';
            if (class_exists($try)) {
                $callable = $try.'::'.$action.'Action';
                if (is_callable($callable)) {
                    $this->logger->debug(
                        sprintf('theme resolver: resolved "%s" controller into %s', $input_controller, $callable)
                    );

                    return $callable;
                }
            }

            if ($parent = $theme->getParent()) {
                return $this->controller($parent, $input_controller);
            }

            throw new \RuntimeException(sprintf('could not resolve theme controller "%s"', $input_controller));
        }

        return;
    }

    /**
     * Get the absolute path to a filename for a theme, with the name format like:
     * Theme:Portal:index.html.twig
     * ThemeParent:Portal:index.html.twig.
     *
     * @param ThemeInterface $theme
     * @param                $name
     *
     * @return string
     */
    public function templatePath(ThemeInterface $theme, $name)
    {
        if (!is_string($name) || 3 !== count($parts = explode(':', $name))) {
            return;
        }

        if ('Theme:' === substr($name, 0, 6) || 'ThemeTagTemplate::' === substr($name, 0, 18)) {
            return $this->getThemeTemplatePath($theme, $name);
        }

        // you can refer to the parent theme by prefixing "ThemeParent:" instead of "Theme:"
        if ('ThemeParent:' === substr($name, 0, 12)) {
            if ($parent = $theme->getParent()) {
                return $this->templatePath($parent, 'Theme:'.substr($name, 12));
            }
        }

        return;
    }

    public function getThemeTemplateMap()
    {
        if (isset($this->themeTemplateMap)) {
            return $this->themeTemplateMap;
        }

        $this->logger->debug('theme resolver: creating template map');

        // get the cache
        $mapCache = $this->container->get('portal_cache.template_map');

        // if file exists, use it
        if (file_exists($mapCache)) {
            $this->logger->debug('theme resolver: found template map in cache file '.$mapCache);

            return $this->themeTemplateMap = require $mapCache;
        }

        $this->logger->debug('theme resolver: cache file not found, generating and saving to '.$mapCache);
        // not fresh, let's gen the whole map
        // each theme will be resolved now...
        $this->themeTemplateMap = [];
        foreach ($this->theme_repo->findAll() as $theme) {
            $this->themeTemplateMap[$theme->getId()] = $theme->getTemplateMap();
        }

        $mapCache->write('<?php return '.var_export($this->themeTemplateMap, true).';');

        return $this->themeTemplateMap;
    }

    /**
     * @param ThemeInterface $theme
     * @param string         $name
     *
     * @return null|string
     */
    protected function getThemeTemplatePath(ThemeInterface $theme, $name)
    {
        $map = $this->getThemeTemplateMap();
        if (isset($map[$theme->getId()])
            && isset($map[$theme->getId()][$name])
        ) {
            return realpath(DP_ROOT.$map[$theme->getId()][$name]);
        }

        return;
    }

    public function getThemeSetTemplateFromDb(ThemeSet $theme_set, $template_name)
    {
        /** @var \Application\DeskPRO\EntityRepository\Template $template_repo */
        $template_repo = $this->em->getRepository('DeskPRO:Template');

        return $template_repo->getThemeSetTemplate($template_name, $theme_set);
    }

    /**
     * @param $themeId
     *
     * @return ThemeInterface
     */
    public function getThemeById($themeId)
    {
        if (!$themeId) {
            return;
        }
        if ($themeId instanceof ThemeSet) {
            $themeId = $themeId->getThemeId();
        }

        return $this->theme_repo->find($themeId);
    }

    /**
     * @param ThemeInterface $theme
     * @param $tag_name
     * @param array $arguments
     *
     * @return string
     */
    public function processTag(ThemeInterface $theme, $tag_name, array $arguments)
    {
        if (!$tag = $theme->resolveTag($tag_name)) {
            throw new \InvalidArgumentException("Unknown tag: $tag_name");
        }

        return $this->tag_processor->process($tag, $arguments);
    }

    /**
     * @param ThemeInterface $theme
     * @param $tag_name
     *
     * @return bool
     */
    public function hasTag(ThemeInterface $theme, $tag_name)
    {
        if (!$theme->resolveTag($tag_name)) {
            return false;
        }

        return true;
    }
}
