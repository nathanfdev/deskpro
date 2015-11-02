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
namespace DeskPRO\Bundle\AppBundle\DataFixtures\DevFixtures;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class LegacyFixture extends AbstractFixture implements ContainerAwareInterface, OrderedFixtureInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * {@inheritdoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return -10;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $em        = $this->container->get('doctrine.orm.default_entity_manager');
        $translate = $this->container->get('deskpro.core.translate');

        $USERGROUP_EVERYONE = $em->getRepository('DeskPRO:Usergroup')->findOneBy(array('sys_name' => 'everyone'));

        require DP_ROOT.'/src/Application/InstallBundle/Data/data.php';
        $em->flush();

        if (!empty($USERGROUP_EVERYONE)) {
            $scanner = new \Application\InstallBundle\Data\UserGroupPermScanner();
            foreach ($scanner->getNames() as $p_name) {
                $p            = new \Application\DeskPRO\Entity\Permission();
                $p->usergroup = $USERGROUP_EVERYONE;
                $p->name      = $p_name;
                $p->value     = 1;
                $em->persist($p);
            }
            $em->flush();
        }
    }
}
