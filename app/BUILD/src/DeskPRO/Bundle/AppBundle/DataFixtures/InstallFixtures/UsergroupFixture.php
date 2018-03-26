<?php

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

        $ug = new Usergroup();
        $ug->setTitle($tr->phrase('agent.defaults.usergroup_everyone'));
        $ug->setNote($tr->phrase('agent.defaults.usergroup_everyone_note'));
        $ug->setSysName(Usergroup::EVERYONE);

        $manager->persist($ug);
        $this->addReference('usergroup.everyone', $ug);

        $ug = new Usergroup();
        $ug->setTitle($tr->phrase('agent.defaults.usergroup_registered'));
        $ug->setNote($tr->phrase('agent.defaults.usergroup_registered_note'));
        $ug->setSysName(Usergroup::REGISTERED);

        $manager->persist($ug);
        $this->addReference('usergroup.registered', $ug);

        $ug = new Usergroup();
        $ug->setTitle($tr->phrase('agent.defaults.usergroup_agent_all_perms'));
        $ug->setNote($tr->phrase('agent.defaults.usergroup_agent_all_perms_note'));
        $ug->setIsAgentGroup(true);
        $ug->setSysName(Usergroup::AGENT_ALL_PERM);

        $manager->persist($ug);
        $this->addReference('usergroup.agent_all_perms', $ug);

        $ug = new Usergroup();
        $ug->setTitle($tr->phrase('agent.defaults.usergroup_agent_all_non_destructive'));
        $ug->setNote($tr->phrase('agent.defaults.usergroup_agent_all_non_destructive_note'));
        $ug->setIsAgentGroup(true);
        $ug->setSysName(Usergroup::AGENT_ALL_SAFE_PERM);

        $manager->persist($ug);
        $this->addReference('usergroup.agent_all_safe_perms', $ug);

        if ($this->hasReference('admin')) {
            /** @var \Application\DeskPRO\Entity\Person $admin */
            $admin = $this->getReference('admin');
            $admin->addUsergroup($this->getReference('usergroup.agent_all_perms'));
            $manager->persist($admin);
        }

        $manager->flush();
    }
}
