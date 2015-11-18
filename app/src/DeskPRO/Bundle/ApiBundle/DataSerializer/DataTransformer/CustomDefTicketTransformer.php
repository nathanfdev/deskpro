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

use DeskPRO\Bundle\ApiBundle\DataSerializer\DataTransformerRequest;

/**
 * Class CustomDefTicketTransformer.
 */
class CustomDefTicketTransformer extends AbstractDataSerializerTransformer
{
    /**
     * {@inheritdoc}
     */
    public function getAutomaticProperties(DataTransformerRequest $transformation_request)
    {
        // note we expose widget_type here NOT the class name
        // the API users dont need to know implementation details, specifically
        // around choice fields with the weirdness with expanded/multiple options
        // its easier to just call them what people expect (choice, multichoice, radio, checkbox).

        return [
            'id',
            'widget_type',
            'title',
            'description',
            'options',
            'is_user_enabled',
            'is_enabled',
            'display_order',
            'default_value',
            'is_agent_field',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getCustomProperties(DataTransformerRequest $transformation_request)
    {
        /** @var \Application\DeskPRO\Entity\CustomDefTicket $f */
        $f = $transformation_request->getDataToBeTransformed();

        if ($f->getTypeName() !== 'choice') {
            return [];
        }

        // Format choices into hierarchy

        $map      = [];
        $children = $f->getChildren();
        foreach ($children as $c) {
            $pid = (int) $c->getOption('parent_id', 0);
            if (!isset($map[$pid])) {
                $map[$pid] = [];
            }
            $map[$pid][$c->getId()] = $c;
        }

        $iter = function ($parent_id, $depth = 0) use ($map, &$iter) {
            if (empty($map[$parent_id])) {
                return [];
            }

            $level_choices = [];
            foreach ($map[$parent_id] as $c) {
                $subs = $iter($c->getId(), $depth + 1);
                $row  = [
                    'id'            => $c->getId(),
                    'title'         => $c->getTitle(),
                    'is_selectable' => empty($subs),
                ];

                if ($subs) {
                    $row['children'] = $subs;
                }

                $level_choices[] = $row;
            }

            return $level_choices;
        };

        $choices = $iter(0);

        return ['choices' => $choices];
    }
}
