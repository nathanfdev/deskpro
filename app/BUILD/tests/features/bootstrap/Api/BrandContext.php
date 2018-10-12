<?php

namespace DpBehat\Api;

use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Setting;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use DeskPRO\Bundle\AppBundle\Entity\ThemeSet;
use DpBehat\BaseContext;
use DpBehat\Data\DataContext;

/**
 * Class BrandContext.
 */
class BrandContext extends BaseContext
{
    /**
     * @var DataContext
     * */
    private $dataContext;

    /**
     * @BeforeScenario
     *
     * @param BeforeScenarioScope $scope
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $environment       = $scope->getEnvironment();
        $this->dataContext = $environment->getContext('DpBehat\Data\DataContext');
    }

    /**
     * @Given I have several brands
     */
    public function iHaveSeveralBrand()
    {
        $brand = $this->createNewBrand('Other Brand');
        DataContext::setPlaceholder('otherBrandId', $brand->getId());
    }

    /**
     * @Given I have only default brand
     */
    public function iHaveDefaultBrand()
    {
        $this->dataContext->noRecordsExist('Brand');
        $brand = $this->createNewBrand('default');

        /** @var \Application\DeskPRO\EntityRepository\Setting $settingRepo */
        $settingRepo = $this->em()->getRepository(Setting::class);
        $settingRepo->updateSetting('portal.default_brand', $brand->getId());
        $this->get('settings_resolver')->getGlobalSettings(true);

        DataContext::setReference('defaultBrand', $brand);
        DataContext::setPlaceholder('defaultBrandId', $brand->getId());

        return $brand;
    }

    /**
     * @Given :person has :brand brand
     *
     * @param string $person
     * @param string $brand
     */
    public function personHasBrand($person, $brand)
    {
        /** @var Person $person */
        $person = DataContext::getReference($person);
        $brand  = DataContext::getReference($brand);

        $person->addBrand($brand);
        $this->em()->persist($person);
        $this->em()->flush();
    }

    /**
     * @param string $name
     *
     * @return Brand
     */
    protected function createNewBrand($name)
    {
        $brand = new Brand();

        $themeSet = new ThemeSet();
        $themeSet->setThemeId('standard');
        $this->em()->persist($themeSet);

        $editThemeSet = new ThemeSet();
        $editThemeSet->setThemeId('standard');
        $this->em()->persist($editThemeSet);

        $brand->setName($name);
        $brand->setThemeSet($themeSet);
        $brand->setEditThemeSet($editThemeSet);

        $this->em()->persist($brand);
        $this->em()->flush();

        return $brand;
    }
}
