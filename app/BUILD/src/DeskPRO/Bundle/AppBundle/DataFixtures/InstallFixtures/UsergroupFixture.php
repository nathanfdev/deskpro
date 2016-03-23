<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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
namespace DeskPRO\Bundle\AppBundle\DataFixtures\InstallFixtures;

use Application\DeskPRO\Entity\Usergroup;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class UsergroupFixture extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{
    use ContainerAwareTrait;

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 10;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        /** @var \Application\DeskPRO\Translate\Translate $tr */
        $tr = $this->container->get('deskpro.core.translate');

        $g           = new Usergroup();
        $g->title    = $tr->phrase('agent.defaults.usergroup_everyone');
        $g->note     = $tr->phrase('agent.defaults.usergroup_everyone_note');
        $g->sys_name = 'everyone';
        $manager->persist($g);
        $this->addReference('usergroup.everyone', $g);

        $g           = new Usergroup();
        $g->title    = $tr->phrase('agent.defaults.usergroup_registered');
        $g->note     = $tr->phrase('agent.defaults.usergroup_registered_note');
        $g->sys_name = 'registered';
        $manager->persist($g);
        $this->addReference('usergroup.registered', $g);

        $g                 = new Usergroup();
        $g->title          = $tr->phrase('agent.defaults.usergroup_agent_all_perms');
        $g->note           = $tr->phrase('agent.defaults.usergroup_agent_all_perms_note');
        $g->is_agent_group = true;
        $g->sys_name       = 'agent_all_perms';
        $manager->persist($g);
        $this->addReference('usergroup.agent_all_perms', $g);

        $g                 = new Usergroup();
        $g->title          = $tr->phrase('agent.defaults.usergroup_agent_all_non_destructive');
        $g->note           = $tr->phrase('agent.defaults.usergroup_agent_all_non_destructive_note');
        $g->is_agent_group = true;
        $g->sys_name       = 'agent_all_safe_perms';
        $manager->persist($g);
        $this->addReference('usergroup.agent_all_safe_perms', $g);

        if ($this->hasReference('admin')) {
            /** @var \Application\DeskPRO\Entity\Person $admin */
            $admin = $this->getReference('admin');
            $admin->addUsergroup($this->getReference('usergroup.agent_all_perms'));
            $manager->persist($admin);
        }

        $manager->flush();
    }
}
