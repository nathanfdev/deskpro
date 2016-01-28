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

namespace DeskPRO\Bundle\PortalBundle\Form\Form\DataTransformer;

use Application\DeskPRO\Domain\DomainObject;
use DeskPRO\Bundle\AppBundle\Entity\EntityInterface;
use DeskPRO\Component\Hierarchy\HierarchyNode;
use DeskPRO\Component\Util\EntityUtils;
use Symfony\Component\Form\ChoiceList\LegacyChoiceListAdapter;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Extension\Core\ChoiceList\ChoiceListInterface;

class HierarchyNodeTransformer implements DataTransformerInterface
{
    /**
     * @var ChoiceListInterface
     */
    private $choice_list;

    /**
     * @var bool
     */
    private $multiple;

    public function __construct(LegacyChoiceListAdapter $choice_list, $multiple = false)
    {
        $this->choice_list = $choice_list;
        $this->multiple    = $multiple;
    }

    public function transform($value)
    {
        if (!$value) {
            return '';
        }

        $choices = $this->choice_list->getChoices();

        if (count($choices) < 1) {
            return '';
        }

        /** @var \DeskPRO\Bundle\PortalBundle\Form\Hierarchy\HierarchyNode $choice */
        foreach ($choices as $choice) {
            if (!$choice instanceof HierarchyNode) {
                continue;
            }

            $data = $choice->getData();
            if ($data instanceof DomainObject || $data instanceof EntityInterface) {
                $data = EntityUtils::getIdentifier($data);
            }

            if ($value instanceof DomainObject || $value instanceof EntityInterface) {
                $value = EntityUtils::getIdentifier($value);
            }

            if ($value === $data) {
                return $choice;
            }
        }

        return '';
    }

    public function reverseTransform($value)
    {
        if ($value instanceof HierarchyNode) {
            return $value->getData();
        }
    }
}
