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

use DeskPRO\Bundle\AppBundle\ActionEngine\OptionsResolver\ActionOptionsResolver;

abstract class AbstractAction implements ActionInterface
{
    const APPROVE_ACTION             = 'approve';
    const DELETE_ACTION              = 'delete';
    const SET_STATUS_CATEGORY_ACTION = 'set_status_category';
    const SET_HIDDEN_STATUS_ACTION   = 'set_hidden_status';
    const SET_TYPE_ACTION            = 'set_type';
    const SET_CATEGORY_ACTION        = 'set_category';
    const ADD_LABELS_ACTION          = 'add_labels';
    const REMOVE_LABELS_ACTION       = 'remove_labels';

    const OPTION_LABELS = 'labels';
    const OPTION_INPUT  = 'input';
    const OPTION_ID     = 'id';

    protected $options;

    public function __construct(array $options = null)
    {
        if (null !== $options) {
            $resolver = new ActionOptionsResolver();
            static::configureOptions($resolver);
            $this->options = $resolver->resolve($options);
        }
    }

    /**
     * Configure your options here. You MUST override this method, if you are implementing ActionWithOptionsInterface.
     *
     * @param ActionOptionsResolver $resolver
     */
    public static function configureOptions(ActionOptionsResolver $resolver)
    {
        throw new \RuntimeException(
            'Action\'s extending AbstractAction and implementing ActionWithOptionsInterface must override the "configureOptions" method'
        );
    }

    /** @return array */
    public function serialize()
    {
        return ['options' => $this->options];
    }
}
