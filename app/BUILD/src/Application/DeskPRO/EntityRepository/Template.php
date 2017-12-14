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
 *
 * @category Entities
 */

namespace Application\DeskPRO\EntityRepository;

use Application\DeskPRO\Entity\Template as TemplateEntity;
use Application\DeskPRO\Entity\TicketTrigger as TicketTriggerEntity;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSet;
use Doctrine\ORM\Query\Expr\Join;

class Template extends AbstractEntityRepository
{
    /**
     * @var array of loaded db templates by brand id. example:[$brand_id][$theme_id][$template_name]
     */
    protected $loadedTemplates;

    /**
     * @param $name
     *
     * @return null|\Application\DeskPRO\Entity\Template
     */
    public function getTemplateByName($name)
    {
        return $this->findOneBy(['name' => $name]);
    }

    /**
     * @param      $template_name
     * @param null $style
     *
     * @return mixed|null
     * @depreated - still in use so cant delete, but styles dont exist anymore so will always return null
     */
    public function getTemplateForStyle($template_name, $style = null)
    {
        try {
            if ($style === null or $style === 0) {
                $q = $this->getEntityManager()->createQuery('
                    SELECT t
                    FROM DeskPRO:Template t
                    WHERE t.style IS NULL AND t.name = ?1
                ')->setParameters([1 => $template_name]);
            } else {
                $q = $this->getEntityManager()->createQuery('
                    SELECT t
                    FROM DeskPRO:Template t
                    WHERE t.style = ?1 AND t.name = ?2
                ')->setParameters([1 => $style, 2 => $template_name]);
            }

            $r = $q->getSingleResult();

            return $r;
        } catch (\Exception $e) {
            return;
        }
    }

    /**
     * @param $style
     *
     * @return array
     *
     * @deprecated
     */
    public function getCustomTemplateNamesInStyle($style)
    {
        return [];
    }

    /**
     * @param $style
     *
     * @return array
     *
     * @deprecated
     */
    public function getCustomTemplateInfoInStyle($style)
    {
        return [];
    }

    /**
     * @param          $name
     * @param ThemeSet $theme_set
     *
     * @return null|TemplateEntity
     */
    public function getThemeSetTemplate($name, ThemeSet $theme_set)
    {
        $theme_set_id = $theme_set->getId();
        $loaded       = $this->getLoadedTemplatesForThemeSet($theme_set);
        if (isset($loaded[$theme_set_id])) {
            return isset($loaded[$theme_set_id][(string) $name]) ? $loaded[$theme_set_id][(string) $name] : null;
        }

        return;
    }

    public function getLoadedTemplatesForThemeSet(ThemeSet $theme_set)
    {
        $theme_set_id = $theme_set->getId();
        if (!isset($this->loadedTemplates[$theme_set_id])) {
            $this->loadedTemplates[$theme_set_id] = $this->loadTemplates($theme_set);
        }

        return $this->loadedTemplates[$theme_set_id];
    }

    protected function loadTemplates(ThemeSet $theme_set)
    {
        $found_brand_templates = $this->findBy(
            [
                'theme_set' => $theme_set,
            ]
        );

        $theme_set_id         = $theme_set->getId();
        $saved                = [];
        $saved[$theme_set_id] = [];

        /** @var \Application\DeskPRO\Entity\Template $template */
        foreach ($found_brand_templates as $template) {
            $saved[$theme_set_id][$template->getName()] = $template;
        }

        return $saved;
    }

    public function getLegacyTemplates()
    {
        $qb = $this->getEntityManager()->createQueryBuilder();
        $qb
            ->select('t', 'GROUP_CONCAT(tt.event_trigger, \'-\', tt.id)')
            ->from(TemplateEntity::class, 't')
            ->where('t.name LIKE :name')
            ->leftJoin(TicketTriggerEntity::class, 'tt', Join::WITH, 'tt.actions LIKE CONCAT(\'%"template":"\', t.name, \'"%\')')
            ->groupBy('t.id')
            ->setParameter('name', 'DeskPRO:emails_%')
        ;

        return $qb->getQuery()->getResult();
    }
}
