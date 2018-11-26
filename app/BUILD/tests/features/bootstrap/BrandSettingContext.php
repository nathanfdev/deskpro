<?php

/**
 * DeskPRO.
 */

namespace DpBehat;

use Application\DeskPRO\Entity\Brand;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use DeskPRO\Bundle\BrandBundle\Brand\BrandStack;
use DpBehat\Data\DataContext;

class BrandSettingContext extends BaseContext
{
    /** @var DataContext */
    private $dataContext;

    /**
     * @Given refresh brand stack
     */
    public function refreshBrandStack()
    {
        /** @var BrandStack $stack */
        $stack = $this->get('brand_stack');
        for ($i = 0; $i < count($stack->getStack()); ++$i) {
            $stack->pop();
        }

        foreach ($this->getEm()->getRepository(Brand::class)->findAll() as $brand) {
            $stack->push($brand);
        }
    }

    /**
     * @param BeforeScenarioScope $scope
     * @BeforeScenario
     */
    public function gatherContexts(BeforeScenarioScope $scope)
    {
        $environment       = $scope->getEnvironment();
        $this->dataContext = $environment->getContext('DpBehat\Data\DataContext');
    }

    /**
     * @Given only setting for brand :brand with name :name and value :value exists
     *
     * @param string $brand
     * @param string $name
     * @param string $value
     */
    public function onlySettingWithNameAndValueForBrandExists($brand, $name, $value)
    {
        $this->dataContext->noRecordsExist('BrandSetting');
        $this->settingWithNameAndValueForBrandExists($brand, $name, $value);
    }

    /**
     * @Given setting for brand :brand with name :name and value :value exists
     *
     * @param string $brand
     * @param string $name
     * @param string $value
     */
    public function settingWithNameAndValueForBrandExists($brand, $name, $value)
    {
        $brand = DataContext::getReference(trim($brand, '{}~'));
        if (DataContext::hasReference(trim($value, '{}~'))) {
            $value = DataContext::getReference(trim($value, '{}~'))->getId();
        }
        $table = [
            ['brand', 'name', 'value'],
            [$brand, $name, $value],
        ];
        $this->dataContext->theFollowingRecordsExist('BrandSetting', new TableNode($table));
    }

    /**
     * @param TableNode $table
     *
     * @Given only the following brand settings records exist:
     */
    public function onlyFollowingBrandSettingsExists(TableNode $table)
    {
        $this->dataContext->noRecordsExist('BrandSetting');
        $recordsData = $table->getHash();
        foreach ($recordsData as $data) {
            if (
                !array_key_exists('brand', $data)
                || !array_key_exists('name', $data)
                || !array_key_exists('value', $data)
            ) {
                throw new \RuntimeException('Table should have brand, name and value rows');
            }
            $this->settingWithNameAndValueForBrandExists($data['brand'], $data['name'], $data['value']);
        }
    }
}
