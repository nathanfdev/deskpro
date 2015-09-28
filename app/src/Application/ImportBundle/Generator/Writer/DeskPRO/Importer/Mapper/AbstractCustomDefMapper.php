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

namespace Application\ImportBundle\Generator\Writer\DeskPRO\Importer\Mapper;

use Application\DeskPRO\Entity\CustomDefAbstract;

/**
 * Class AbstractCustomDefMapper.
 */
abstract class AbstractCustomDefMapper implements MapperInterface
{
    /**
     * Find a choice custom def entity
     * We store value for choice custom fields like "A > A1".
     *
     * MyField
     *   Option A
     *     |- Option A1
     *     |- Option A2
     *   Option B
     *     |- Option B1
     *     |- Option B2
     *
     * @param array|string      $choice_chain
     * @param CustomDefAbstract $parent
     *
     * @return CustomDefAbstract
     */
    public function findChoiceCustomDef($choice_chain, CustomDefAbstract $parent)
    {
        if (is_string($choice_chain)) {
            $choice_chain = explode('>', $choice_chain);
            $choice_chain = array_map('trim', $choice_chain);
        }

        $custom_field_def = $this->findOneBy(array(
            'title'  => array_shift($choice_chain),
            'parent' => $parent,
        ));

        return empty($choice_chain) ? $custom_field_def : $this->findChoiceCustomDef($choice_chain, $custom_field_def);
    }
}
