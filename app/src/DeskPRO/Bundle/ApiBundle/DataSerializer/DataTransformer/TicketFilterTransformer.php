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
namespace DeskPRO\Bundle\ApiBundle\DataSerializer\DataTransformer;

use Application\DeskPRO\Entity\Person;
use DeskPRO\Bundle\ApiBundle\Controller\Tickets\Filters\TicketFiltersController;
use DeskPRO\Bundle\ApiBundle\DataSerializer\DataTransformerRequest;
use DeskPRO\Bundle\AppBundle\Entity\PersonSetting;
use DeskPRO\Bundle\AppBundle\Entity\TicketFilter;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * Class TicketFilterTransformer.
 */
class TicketFilterTransformer extends AbstractDataSerializerTransformer
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var Person
     */
    private $user;

    /**
     * @param TokenStorage  $token_storage
     * @param EntityManager $em
     */
    public function __construct(TokenStorage $token_storage, EntityManager $em)
    {
        $this->user = $token_storage->getToken() ? $token_storage->getToken()->getUser() : null;
        $this->em   = $em;
    }

    /**
     * {@inheritdoc}
     */
    public function getAutomaticProperties(DataTransformerRequest $transformation_request)
    {
        return [
            'id',
            'title',
            'term',
            'display_order',
            'filter_set',
            'filter_views',
            'filter_preferences',
            'date_created',
            'date_updated',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomProperties(DataTransformerRequest $transformation_request)
    {
        /* @var \DeskPRO\Bundle\AppBundle\Entity\TicketFilter $filter */
        $filter = $transformation_request->getDataToBeTransformed();
        $custom = [];

        if ($this->user) {
            $custom['group_by'] = $this->getFilterGroupBy($filter);
        }

        return $custom;
    }

    /**
     * @param TicketFilter $filter
     *
     * @return string|null
     */
    private function getFilterGroupBy(TicketFilter $filter)
    {
        $setting = $this->em->find(PersonSetting::class, [
            'name'   => TicketFiltersController::CUSTOM_FILTER_GROUP_BY_PREFIX.$filter->getId(),
            'person' => $this->user,
        ]);

        return $setting ? $setting->getValue() : null;
    }
}
