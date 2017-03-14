<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
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

use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class AgentChatFeature.
 */
class AgentChatFeature extends AbstractFeature
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'agent_chat';
    }

    /**
     * {@inheritdoc}
     */
    public function getTitle()
    {
        return 'Agent IM v2';
    }

    /**
     * {@inheritdoc}
     */
    public function getShortDescription()
    {
        return 'Improved agent instant messaging';
    }

    /**
     * {@inheritdoc}
     */
    public function getEnableDescription()
    {
        return <<<'HTML'
Agent IM v2 replaces the current agent instant messaging feature with a new and improved version that is more powerful
 and easier to use.<br/><br/>
Installing Agent IM v2 beta will copy your old conversations over to the new system and then disable the old 
messaging system. If you later decide you wish to go back to the old system, you can disable IM v2.

HTML;
    }

    /**
     * {@inheritdoc}
     */
    public function getDisableDescription()
    {
        return <<<'HTML'
Disabling IM v2 will revert your helpdesk back to using the previous instant messaging system.<br/><br/>
Please note that any new conversations you have started or contunued on the IM v2 system will <strong>NOT</strong> be
 copied back to the old system. New conversations will be <strong>lost</strong> forever.
HTML;
    }

    /**
     * {@inheritdoc}
     */
    public function enable(ContainerInterface $container)
    {
        // TODO: Implement enable() method.
    }

    /**
     * {@inheritdoc}
     */
    public function disable(ContainerInterface $container)
    {
        // TODO: Implement disable() method.
    }
}
