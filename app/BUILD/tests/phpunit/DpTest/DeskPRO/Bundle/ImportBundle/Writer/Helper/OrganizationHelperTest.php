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

namespace DpTest\DeskPRO\Bundle\ImportBundle\Writer\Helper;

use Application\DeskPRO\Entity\Organization;
use DeskPRO\Bundle\ImportBundle\Model;
use DeskPRO\Bundle\ImportBundle\Writer\Helper\OrganizationHelper;
use DpTest\DeskPRO\Bundle\ImportBundle\Writer\AbstractWriterTest;

/**
 * Class OrganizationHelper.
 */
class OrganizationHelperTest extends AbstractWriterTest
{
    /**
     * @var OrganizationHelper
     */
    private $helper;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->helper = $this->getContainer()->get('dp.importer.writer.helper.organization');
        $this->clearTable('organizations');

        parent::setUp();
    }

    public function test_import_new_organization_by_oid()
    {
        $organization = $this->helper->findOrCreateOrganization(1);

        $this->assertNotNull($organization->getId());
        $this->assertEquals('Organization1', $organization->getName());
    }

    public function test_import_new_organization_by_name()
    {
        $organization = $this->helper->findOrCreateOrganization('my_org');

        $this->assertNotNull($organization->getId());
        $this->assertEquals('my_org', $organization->getName());
    }

    public function test_existing_organization_by_name()
    {
        $organization = new Organization();
        $organization->setName('name');

        $this->em()->persist($organization);
        $this->em()->flush();

        $helperOrganization = $this->helper->findOrCreateOrganization('name');

        $this->assertEquals($organization->getId(), $helperOrganization->getId());
        $this->assertEquals('name', $helperOrganization->getName());
    }

    public function test_existing_organization_by_oid()
    {
        $organization = new Organization();
        $organization->setName('name');

        $this->em()->persist($organization);
        $this->em()->flush();

        $orgModel = new Model\Organization();
        $orgModel->setOid(5);

        $this->getContainer()->get('dp.importer.writer.mapper.import_map')->saveMapping($orgModel, $organization);

        $helperOrg = $this->helper->findOrCreateOrganization(5);

        $this->assertEquals($organization->getId(), $helperOrg->getId());
        $this->assertEquals('name', $helperOrg->getName());
    }
}
