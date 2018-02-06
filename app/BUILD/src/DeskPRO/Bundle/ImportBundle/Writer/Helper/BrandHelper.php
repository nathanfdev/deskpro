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

namespace DeskPRO\Bundle\ImportBundle\Writer\Helper;

use Application\DeskPRO\Entity\Brand;
use DeskPRO\Bundle\ImportBundle\Writer\EntityPersister;
use DeskPRO\Bundle\ImportBundle\Writer\Mapper\BrandMapper;
use DeskPRO\Bundle\ImportBundle\Writer\Mapper\ImportMapMapper;
use Psr\Log\LoggerInterface;

/**
 * Class BrandHelper.
 */
class BrandHelper
{
    /**
     * @var ImportMapMapper
     */
    private $importMapMapper;

    /**
     * @var BrandMapper
     */
    private $brandMapper;

    /**
     * @var EntityPersister
     */
    private $persister;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * Constructor.
     *
     * @param ImportMapMapper $importMapMapper
     * @param BrandMapper     $brandMapper
     * @param EntityPersister $persister
     * @param LoggerInterface $logger
     */
    public function __construct(
        ImportMapMapper  $importMapMapper,
        BrandMapper      $brandMapper,
        EntityPersister  $persister,
        LoggerInterface  $logger
    ) {
        $this->importMapMapper = $importMapMapper;
        $this->brandMapper     = $brandMapper;
        $this->persister       = $persister;
        $this->logger          = $logger;
    }

    /**
     * Returns a brand by title.
     * Creates a new brand if not found.
     *
     * @param string $brandName
     *
     * @return Brand
     */
    public function findOrCreateBrand($brandName)
    {
        if (!$brandName) {
            throw new \RuntimeException('Brand name is empty');
        }

        $brand = $this->brandMapper->findByName($brandName);
        if (!$brand) {
            $brand = new Brand();
            $brand->setName($brandName);

            $this->persister->persistAndFlush($brand);
        }

        return $brand;
    }

    /**
     * @return Brand
     */
    public function getDefaultBrand()
    {
        return $this->brandMapper->getDefaultBrand();
    }
}
