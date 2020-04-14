<?php

namespace DpTest\DeskPRO\Application\EntityRepository;

use Application\DeskPRO\EntityRepository\Organization as OrganizationRepository;
use Application\DeskPRO\EntityRepository\AbstractEntityRepository;

class OrganizationTest extends \PHPUnit_Framework_TestCase
{
    public function testGetReportAssociations()
    {
        $mockEm = \Mockery::mock('Doctrine\ORM\EntityManager');
        $metadata = \Mockery::mock('Doctrine\ORM\Mapping\ClassMetadata');
        $repo = new OrganizationRepository($mockEm, $metadata);

        $associations = $repo->getReportAssociations();

        $this->assertArrayHasKey('organization_usergroup', $associations);
        $this->assertInstanceOf(AbstractEntityRepository::class, $associations['organization_usergroup']['repository']);
    }
}
