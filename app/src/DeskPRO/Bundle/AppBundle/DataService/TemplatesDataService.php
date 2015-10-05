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
namespace DeskPRO\Bundle\AppBundle\DataService;

use Application\DeskPRO\Entity\Template;

class TemplatesDataService extends AbstractDataService
{
    /**
     * @param int|null|Template $template
     *
     * @return Template|null
     */
    public function getTemplate($template)
    {
        $that = $this;

        return $this->generateAndCache(
            array(
                'getTemplate',
                $template,
            ),
            function () use ($that, $template) {
                if (!$template) { // we need some input
                    return;
                }

                if ($template instanceof Template) { // already have what you seek
                    return $template;
                }

                return $that->getTemplateRepo()->findOneBy(array('name' => $template));
            }
        );
    }

    /**
     * @return \Application\DeskPRO\EntityRepository\Template
     */
    protected function getTemplateRepo()
    {
        return $this->em->getRepository('DeskPRO:Template');
    }
}
