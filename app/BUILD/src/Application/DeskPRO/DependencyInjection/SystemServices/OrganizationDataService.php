<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace Application\DeskPRO\DependencyInjection\SystemServices;

use Application\DeskPRO\DependencyInjection\DeskproContainer;
use Application\DeskPRO\Entity\Organization;

/**
 * Class OrganizationDataService.
 *
 * @method string[] getOrganizationNames
 */
class OrganizationDataService extends BaseRepositoryService
{
    /** @var bool */
    protected $has_init = false;
    /** @var array */
    protected $cats;
    /** @var array */
    protected $cat_ids = [];
    /** @var array */
    protected $filtered_nodes = [];

    /**
     * @param DeskproContainer $container
     * @param array|null       $options
     *
     * @return static
     */
    public static function create(DeskproContainer $container, array $options = null)
    {
        if (!$options) {
            $options = [];
        }
        $options['entity'] = Organization::class;

        $em = $container->getEm();
        $o  = new static($em, $options);

        return $o;
    }
}
