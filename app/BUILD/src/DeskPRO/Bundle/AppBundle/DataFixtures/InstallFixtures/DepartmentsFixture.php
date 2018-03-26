<?php

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
