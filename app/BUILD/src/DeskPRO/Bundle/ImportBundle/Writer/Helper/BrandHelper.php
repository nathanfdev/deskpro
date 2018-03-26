<?php

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
