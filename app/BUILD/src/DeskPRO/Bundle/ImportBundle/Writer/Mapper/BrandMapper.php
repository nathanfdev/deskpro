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

namespace DeskPRO\Bundle\ImportBundle\Writer\Mapper;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Department;
use DeskPRO\Bundle\AppBundle\Settings\BrandAwareSettingsResolver;
use DeskPRO\Bundle\AppBundle\Settings\Model\Tickets\DefaultDepartmentSettings;
use Doctrine\ORM\EntityManager;

/**
 * Class BrandMapper.
 */
class BrandMapper extends AbstractContainerMapper
{
    /**
     * @var BrandAwareSettingsResolver
     */
    private $settingsResolver;

    /**
     * Constructor.
     *
     * @param EntityManager              $em
     * @param BrandAwareSettingsResolver $settingsResolver
     */
    public function __construct(EntityManager $em, BrandAwareSettingsResolver $settingsResolver)
    {
        parent::__construct($em);
        $this->settingsResolver = $settingsResolver;
    }

    /**
     * {@inheritdoc}
     */
    public static function getMapperEntityClass()
    {
        return Brand::class;
    }

    /**
     * @param string $name
     *
     * @return Brand
     */
    public function findByName($name)
    {
        $qb = $this->em->createQueryBuilder();
        $qb->select('b');
        $qb->from($this->getEntityClass(), 'b');

        if (is_numeric($name)) {
            $qb->where('b.id = :id');
            $qb->setParameter('id', $name);
        } else {
            $qb->where('b.name LIKE :name OR b.url LIKE :name');
            $qb->setParameter('name', '%'.$name.'%');
        }

        $result = $qb->getQuery()->getResult();

        return $result ? $result[0] : null;
    }

    /**
     * @return Brand
     */
    public function getDefaultBrand()
    {
        return $this->em->getRepository($this->getEntityClass())->findOneBy([]);
    }

    /**
     * @param Brand $brand
     *
     * @return Department
     */
    public function getDefaultDepartment(Brand $brand = null)
    {
        $defaultDepartment = null;

        // get default department from brand settings
        $defaultDepartmentId = $this->settingsResolver->getSetting(
            DefaultDepartmentSettings::constructName(DefaultDepartmentSettings::DEFAULT_DEPARTMENT_USER_TYPE),
            $brand
        );
        if ($defaultDepartmentId) {
            $department = $this->em->getRepository(Department::class)->find($defaultDepartmentId);
            if ($department->isTicketsEnabled()) {
                $defaultDepartment = $department;
            }
        }

        if ($brand && !$defaultDepartment) {
            // get default department as first brand department
            $department = $brand->getDepartments()->filter(function (Department $department) {
                return $department->isTicketsEnabled();
            })->first();

            if ($department) {
                $defaultDepartment = $department;
            }
        }

        // get first department as fallback
        if (!$defaultDepartment) {
            $defaultDepartment = $this->em->getRepository(Department::class)->findOneBy([]);
        }

        // check for leaf department
        if ($defaultDepartment && $defaultDepartment->getChildren()->count()) {
            foreach ($defaultDepartment->getAllChildren() as $childDepartment) {
                if ($childDepartment->isLeaf()) {
                    return $childDepartment;
                }
            }
        }

        return $defaultDepartment;
    }
}
