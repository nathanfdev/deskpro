<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2015, DeskPRO Ltd.
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
namespace DeskPRO\Bundle\PortalBundle\Brand;

use Application\DeskPRO\BlobStorage\DeskproBlobStorage;
use Application\DeskPRO\Entity\Brand;
use Doctrine\ORM\EntityManager;

/**
 * @TODO clean this up + optimise
 */
class BrandAssetLoader
{
    /**
     * @var Brand
     */
    private $brand;

    /**
     * @var EntityManager
     */
    private $em;

    /**
     * @var DeskproBlobStorage
     */
    private $bs;

    /**
     * Constructor.
     *
     * @param Brand              $brand
     * @param EntityManager      $em
     * @param DeskproBlobStorage $bs
     */
    public function __construct(Brand $brand, EntityManager $em, DeskproBlobStorage $bs)
    {
        $this->brand = $brand;
        $this->em    = $em;
        $this->bs    = $bs;
    }

    /**
     * Gets all CSS or SCSS files (used when we compile styles).
     *
     * @return array Array of filename => content
     */
    public function getStylesheets()
    {
        $all = [];

        foreach ($this->em->createQuery("
            SELECT a
            FROM App:BrandAsset
            WHERE a.brand = ?0 AND (a.name LIKE '%.css' OR a.name LIKE '%.scss')
        ")->execute() as $b) {
            $src           = $this->bs->copyBlobRecordToString($b->blob);
            $all[$b->name] = $src;
        }

        return $all;
    }
}
