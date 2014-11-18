<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\Entity\Brand as BrandEntity;
use Application\DeskPRO\Entity\Template as TemplateEntity;
use Application\PortalBundle\Theme\ThemeInterface;

class Template extends AbstractEntityRepository
{
    /**
     * @var array of loaded db templates by brand id. example: $loadedTemplates[$brand_id][$theme_id][$template_name]
     */
    protected $loadedTemplates;

    /**
     * @param $name
     * @return null|\Application\DeskPRO\Entity\Template
     */
    public function getTemplateByName($name)
    {
        return $this->findOneBy(array('name' => $name));
    }


    /**
     * @param             $template_name
     * @param  null       $style
     * @return mixed|null
     * @depreated - still in use so cant delete, but styles dont exist anymore so will always return null
     */
    public function getTemplateForStyle($template_name, $style = null)
    {
        try {
            if ($style === null OR $style === 0) {
                $q = $this->getEntityManager()->createQuery("
                    SELECT t
                    FROM DeskPRO:Template t
                    WHERE t.style IS NULL AND t.name = ?1
                ")->setParameters(array(1=>$template_name));
            } else {
                $q = $this->getEntityManager()->createQuery("
                    SELECT t
                    FROM DeskPRO:Template t
                    WHERE t.style = ?1 AND t.name = ?2
                ")->setParameters(array(1=>$style, 2=>$template_name));
            }

            $r = $q->getSingleResult();

            return $r;
        } catch (\Exception $e) {
            return null;
        }
    }


    /**
     * @param $style
     * @return array
     * @deprecated
     */
    public function getCustomTemplateNamesInStyle($style)
    {
        return array();
    }

    /**
     * @param $style
     * @return array
     * @deprecated
     */
    public function getCustomTemplateInfoInStyle($style)
    {
        return array();
    }


    /**
     * @param                      $name
     * @param  BrandEntity         $brand
     * @param  ThemeInterface      $theme
     * @return null|TemplateEntity
     */
    public function getBrandTemplate($name, BrandEntity $brand, ThemeInterface $theme)
    {
        $loaded = $this->getLoadedTemplatesForBrand($brand);
        $theme_id = $theme->getId();
        if (isset($loaded[$theme_id])) {
            return isset($loaded[$theme_id][(string)$name]) ? $loaded[$theme_id][(string)$name] : null;
        }

        return null;
    }


    public function getLoadedTemplatesForBrand(BrandEntity $brand)
    {
        if (!isset($this->loadedTemplates[$brand->id])) {
            $this->loadedTemplates[$brand->id] = $this->loadTemplates($brand);
        }

        return $this->loadedTemplates[$brand->id];
    }


    protected function loadTemplates(BrandEntity $brand)
    {
        $found_brand_templates = $this->findBy(
            array(
                'brand'    => $brand
            )
        );

        $theme = $brand->theme_id;
        $saved = array();
        //TODO: when we need to, you should get a list of all themes injected so that we can do this for every theme
        //      however, we only need to do that if a brand can switch themes during a request, and I dont think we
        //      will ever need to do that? For now, sticking to current brand theme only.
        $saved[$theme] = array();

        foreach ($found_brand_templates as $template) {
            $saved[$theme][$template->name] = $template;
        }

        return $saved;
    }
}
