<?php

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
