<?php

namespace DeskPRO\Bundle\AppBundle\DataFixtures\DevFixtures;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Department;
use DeskPRO\Bundle\AppBundle\DataFixtures\AbstractDpFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class DepartmentsFixture.
 */
class DepartmentsFixture extends AbstractDpFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 50;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $cnt  = 0;
        $deps = [];

        /** @var Brand $brand */
        $brand = $this->getReference('brand');

        $dep1                     = new Department();
        $dep1->is_tickets_enabled = true;
        $dep1->title              = 'Widgets';
        $dep1->display_order      = $cnt++;
        $dep1->addBrand($brand);
        $brand->addDepartment($dep1);

        $deps[] = $dep1;

        $this->addReference('department.widgets', $dep1);
        $manager->persist($dep1);

        $dep2                     = new Department();
        $dep2->is_tickets_enabled = true;
        $dep2->title              = 'Regulation and Control of Magical Creatures';
        $dep2->display_order      = $cnt++;

        $deps[] = $dep2;

        $this->addReference('department.regulation_and_control', $dep2);
        $manager->persist($dep2);

        $dep2_a                     = new Department();
        $dep2_a->is_tickets_enabled = true;
        $dep2_a->title              = 'Regulation';
        $dep2_a->parent             = $dep2;
        $dep2_a->display_order      = $cnt++;
        $dep2_a->addBrand($brand);
        $brand->addDepartment($dep2_a);

        $deps[] = $dep2_a;

        $this->addReference('department.regulation', $dep2_a);
        $manager->persist($dep2_a);

        $dep2_b                     = new Department();
        $dep2_b->is_tickets_enabled = true;
        $dep2_b->title              = 'Control';
        $dep2_b->parent             = $dep2;
        $dep2_b->display_order      = $cnt++;
        $dep2_b->addBrand($brand);
        $brand->addDepartment($dep2_b);

        $deps[] = $dep2_b;

        $this->addReference('department.control', $dep2_b);
        $manager->persist($dep2_b);

        $dep3                     = new Department();
        $dep3->is_tickets_enabled = true;
        $dep3->title              = 'Hotdogs';
        $dep3->display_order      = $cnt++;
        $dep3->addBrand($brand);
        $brand->addDepartment($dep3);

        $deps[] = $dep3;

        $this->addReference('department.hotdogs', $dep3);
        $manager->persist($dep3);

        $manager->persist($brand);

        $manager->flush();

        // Perms
        $permsissions  = [];
        $usergroup_ids = $this->fetchIds(self::TABLE_USERGROUPS);

        foreach ($deps as $d) {
            foreach ($usergroup_ids as $usergroup_id) {
                $permsissions[] = [
                    'department_id' => $d->getId(),
                    'usergroup_id'  => $usergroup_id,
                    'app'           => 'tickets',
                    'name'          => 'full',
                    'value'         => 1,
                ];
            }
        }

        $this->db->batchInsert('department_permissions', $permsissions, true);
    }
}
