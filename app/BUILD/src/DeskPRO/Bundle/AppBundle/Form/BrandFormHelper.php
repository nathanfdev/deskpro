<?php

namespace DeskPRO\Bundle\AppBundle\Form;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Department;
use DeskPRO\Bundle\AppBundle\Settings\BrandAwareSettingsResolver;
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
     * @var BrandAwareSettingsResolver
     */
    private $settingsResolver;

    /**
     * BrandFormHelper constructor.
     *
     * @param EntityManager              $em
     * @param BrandStack                 $brandStack
     * @param BrandAwareSettingsResolver $settingsResolver
     */
    public function __construct(EntityManager $em, BrandStack $brandStack, BrandAwareSettingsResolver $settingsResolver)
    {
        $this->em               = $em;
        $this->brandStack       = $brandStack;
        $this->settingsResolver = $settingsResolver;
    }

    /**
     * @param string $type
     * @param Brand  $brand
     *
     * @throws \Exception
     *
     * @return Department|null
     */
    public function getDefaultDepartment($type = DefaultDepartmentSettings::DEFAULT_DEPARTMENT_USER_TYPE, Brand $brand = null)
    {
        $brand        = $brand ?: $this->brandStack->getActive()->getBrand();
        $departmentId = $this->settingsResolver->getSetting(DefaultDepartmentSettings::constructName($type), $brand);

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
