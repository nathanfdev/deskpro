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

namespace DeskPRO\Bundle\AppBundle\ActionEngine\Actions;

use Doctrine\ORM\EntityManager;
use Symfony\Component\OptionsResolver\OptionsResolver;

abstract class AbstractAction
{
    const APPROVE_ACTION             = 'approve';
    const DELETE_ACTION              = 'delete';
    const SET_STATUS_CATEGORY_ACTION = 'status_category';
    const SET_TYPE_ACTION            = 'type';
    const SET_CATEGORY_ACTION        = 'category';
    const ADD_LABELS_ACTION          = 'add_labels';
    const REMOVE_LABELS_ACTION       = 'remove_labels';

    protected $em;
    protected $options;

    public function __construct(EntityManager $em = null, array $options = [])
    {
        $this->em = $em;
        if (!empty($options)) {
            $resolver = new OptionsResolver();
            $this->configureOptions($resolver);

            $this->options = $resolver->resolve($options);
        }
    }
}
