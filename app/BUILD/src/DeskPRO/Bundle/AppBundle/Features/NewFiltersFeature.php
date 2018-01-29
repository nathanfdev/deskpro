<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2018, DeskPRO Ltd.
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

namespace DeskPRO\Bundle\AppBundle\Features;

class NewFiltersFeature extends AbstractBetaFeature
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'new_filters';
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return 'New Filters';
    }

    /**
     * {@inheritdoc}
     */
    public function getShortDescription()
    {
        return 'Improved filtering in the agent interface';
    }

    /**
     * {@inheritdoc}
     */
    public function getEnableDescription()
    {
        return 'The new filtering system improves filtering features (such as filter sharing, filter sets) and improves accuracy and real-time functions.';
    }

    /**
     * {@inheritdoc}
     */
    public function getDisableDescription()
    {
        return 'Disabling this feature will return the agent interface to using the previous filtering systme.';
    }

    /**
     * {@inheritdoc}
     */
    public function needAgentReload()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getAvailability()
    {
        return [self::AVAILABLE_AT_QA];
    }
}
