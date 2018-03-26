<?php

namespace DeskPRO\Bundle\AppBundle\Form;

use Application\DeskPRO\Entity\Department;
use DeskPRO\Bundle\AppBundle\Settings\Model\Tickets\DefaultDepartmentSettings;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use Doctrine\ORM\EntityManager;

/**
 * Class BrandFormHelper.
 */
class BrandFormHelper
{
    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var BrandStack
     */
    private $brandStack;

    /**
     * BrandFormHelper constructor.
     *
     * @param EntityManager $em
     * @param BrandStack    $brandStack
     */
    public function __construct(EntityManager $em, BrandStack $brandStack)
    {
        $this->em         = $em;
        $this->brandStack = $brandStack;
    }

    /**
     * @param string $type
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     *
     * @return Department|null
     */
    public function getDefaultDepartment($type = DefaultDepartmentSettings::DEFAULT_DEPARTMENT_USER_TYPE)
    {
        $departmentId = $this->brandStack->getActive()->getSetting(DefaultDepartmentSettings::constructName($type));

        if ($departmentId) {
            return $this->em->find(Department::class, $departmentId);
        }

        return;
    }

    /**
     * @return \Application\DeskPRO\Entity\Brand
     */
    public function getCurrentBrand()
    {
        return $this->brandStack->getActive()->getBrand();
    }
}
