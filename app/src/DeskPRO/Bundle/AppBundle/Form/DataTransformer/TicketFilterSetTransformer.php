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
namespace DeskPRO\Bundle\AppBundle\Form\DataTransformer;

use DeskPRO\Bundle\AppBundle\Entity\TicketFilterSet;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;

/**
 * Convert user input into a TicketFilterSet and vice-versa.
 */
class TicketFilterSetTransformer implements DataTransformerInterface
{
    /**
     * @var EntityManager
     */
    protected $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }

    /**
     * Transforms a TicketFilterSet object into its ID.
     *
     * @param TicketFilterSet|null $set
     *
     * @return int|null
     */
    public function transform($value)
    {
        if (!$value) {
            return;
        }

        return $value->getId();
    }

    /**
     * Transforms an integer (id) into a TicketFilterSet object.
     *
     * @param int $id
     *
     * @throws TransformationFailedException if object TicketFilterSet not found.
     *
     * @return TicketFilterSet|null
     */
    public function reverseTransform($id)
    {
        if (!$id) {
            return;
        }

        $set = $this->em->find('App:TicketFilterSet', $id);

        if (null === $set) {
            throw new TransformationFailedException(sprintf('A filter set of ID "%d" does not exist!', $id));
        }

        return $set;
    }
}
