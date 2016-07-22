<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace DpBehat;

use Application\DeskPRO\Entity\Brand;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use DeskPRO\Bundle\PortalBundle\Brand\BrandStack;
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
