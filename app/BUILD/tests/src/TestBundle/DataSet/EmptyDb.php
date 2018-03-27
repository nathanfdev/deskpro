<?php

/**
 * DeskPRO.
 */

namespace DpTestSrc\TestBundle\DataSet;

use Application\DeskPRO\Entity\Brand;

/**
 * Nothing. The Fresh DB doesn't add any additional data.
 *
 * Class EmptyDb.
 */
class EmptyDb extends AbstractDbSet
{
    /**
     * {@inheritdoc}
     */
    protected function installSet()
    {
        $em = $this->getEm();

        // we need a brand here, but the other db's use data.php which has all the brands
        $brand = new Brand();
        $em->persist($brand);
        $em->flush();
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'empty';
    }
}
