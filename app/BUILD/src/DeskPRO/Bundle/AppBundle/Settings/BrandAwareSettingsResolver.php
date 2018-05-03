<?php

namespace DeskPRO\Bundle\AppBundle\Settings;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\NewSettings\SettingsResolver;
use DeskPRO\Bundle\AppBundle\Settings\Model\Tickets\DefaultDepartmentSettings;
use DeskPRO\Bundle\PortalBundle\Brand\BrandContainer;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
use Doctrine\ORM\EntityManager;

/**
 * This is the settings resolver we should use (app-wide) moving forward.
 *
 * It will get brand settings by default, when available, but can gracefully fall back to
 * global settings in the case that:
 *
 * 1. No BrandStack is available in the container, or
 * 2. There are no brands configured in the BrandStack
 *
 * There are no disadvantages to using this service to get settings, and the advantage is that your
 * code will work with the new brand settings as we increase the use of brands in the code base.
 */
class BrandAwareSettingsResolver
{
    /**
     * @var BrandStack
     */
    private $brand_stack;

    /**
     * @var SettingsResolver
     */
    private $settings_resolver;

    /**
     * @var Brand[]
     */
    private $brands;

    /**
     * Constructor.
     *
     * @param SettingsResolver $settings_resolver
     * @param EntityManager    $em
     * @param BrandStack|null  $brand_stack
     */
    public function __construct(
        SettingsResolver $settings_resolver,
        EntityManager $em,
        BrandStack $brand_stack = null
    ) {
        $this->brand_stack       = $brand_stack;
        $this->settings_resolver = $settings_resolver;
        $this->em                = $em;
    }

    /**
     * @param string $name
     * @param Brand  $brand
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getSetting($name, Brand $brand = null, $default = null)
    {
        if ($this->brand_stack && $this->brand_stack->getActive()) {
            if ($default === null) {
                $default = $this->getGlobalSetting($name);
            }

            return $this->getBrandSetting($name, $brand, $default);
        }

        return $this->getGlobalSetting($name, $default);
    }

    /**
     * Allows to check if a setting is enabled on any brand.
     *
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getAnyBrandSetting($name, $default = null)
    {
        foreach ($this->getBrands() as $brand) {
            $setting = $this->getBrandSetting($name, $brand, $default);
            if ($setting) {
                return $setting;
            }
        }

        return $default;
    }

    /**
     * @return mixed
     */
    public function isChatAvailable()
    {
        return $this->getAnyBrandSetting(WidgetSettingsResolver::CHAT_ENABLED, false);
    }

    /**
     * @return array
     */
    public function getDefaultDepartmentSettings()
    {
        $settings = [];
        foreach ($this->em->getRepository(Brand::class)->findAll() as $brand) {
            $brandContainer = new BrandContainer($brand, $this->settings_resolver->getBrandSettings($brand, true));
            $settings[]     = $this->getDepartmentSetting($brandContainer, 'agent');
            $settings[]     = $this->getDepartmentSetting($brandContainer, 'user');
        }

        return $settings;
    }

    /**
     * @return Brand
     */
    public function getActiveBrand()
    {
        return $this->brand_stack->getActive()->getBrand();
    }

    /**
     * @param BrandContainer $brandContainer
     * @param                $type
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     * @throws \Doctrine\ORM\TransactionRequiredException
     *
     * @return DefaultDepartmentSettings
     */
    private function getDepartmentSetting(BrandContainer $brandContainer, $type)
    {
        $settingName       = sprintf('default_department.%s', $type);
        $departmentSetting = new DefaultDepartmentSettings();
        $departmentSetting
            ->setBrand($brandContainer->getBrand())
            ->setType($type)
            ->setDepartment($this->em->find(Department::class, $brandContainer->getSetting($settingName) ?: 0));

        return $departmentSetting;
    }

    /**
     * @param string $name
     * @param Brand  $brand
     * @param mixed  $default
     *
     * @return mixed
     */
    protected function getBrandSetting($name, Brand $brand = null, $default = null)
    {
        if ($brand) {
            $brandContainer = new BrandContainer($brand, $this->settings_resolver->getBrandSettings($brand));
        } else {
            $brandContainer = $this->brand_stack->getActive();
        }

        if (!$brandContainer) {
            throw new \RuntimeException(
                'tried to access the active brand from the brand_stack but there is no active brand configured
            ');
        }

        return $brandContainer->getSetting($name, $default);
    }

    /**
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    protected function getGlobalSetting($name, $default = null)
    {
        return $this->settings_resolver->getGlobalSettings()->get($name, $default);
    }

    /**
     * @return \Application\DeskPRO\Entity\Brand[]|array
     */
    protected function getBrands()
    {
        if (!$this->brands) {
            $this->brands = $this->em->getRepository(Brand::class)->findAll();
        }

        return $this->brands;
    }

    /**
     * @param string $name
     * @param mixed  $default
     *
     * @return array
     */
    public function getAllBrandsSettings($name, $default = null)
    {
        $result = [];
        foreach ($this->getBrands() as $brand) {
            $result[$brand->getId()] = $this->getBrandSetting($name, $brand, $default);
        }

        return $result;
    }
}
