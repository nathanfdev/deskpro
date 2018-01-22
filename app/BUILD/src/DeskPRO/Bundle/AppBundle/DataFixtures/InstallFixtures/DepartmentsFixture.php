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

namespace DeskPRO\Bundle\AppBundle\DataFixtures\InstallFixtures;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Department;
use DeskPRO\Bundle\AppBundle\DataFixtures\AbstractDpFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class DepartmentsFixture extends AbstractDpFixture implements OrderedFixtureInterface
{
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

        /** @var Brand $brand */
        $brand = $this->getReference('brand');

        foreach ([true, false] as $is_ticket) {
            foreach ([
                     'support' => 'user.defaults.department_support',
                     'sales' => 'user.defaults.department_sales',
                     ] as $id => $phraseId) {
                $dep                     = new Department();
                $dep->title              = $tr->phrase($phraseId);
                $dep->is_tickets_enabled = $is_ticket;
                $dep->is_chat_enabled    = !$is_ticket;
                $dep->addBrand($brand);
                $brand->addDepartment($dep);
                $manager->persist($dep);

                if ($is_ticket) {
                    $this->setReference('department.'.$id, $dep);
                } else {
                    $this->setReference('chat_department.'.$id, $dep);
                }
            }
        }
        $manager->persist($brand);

        $manager->flush();
    }
}
