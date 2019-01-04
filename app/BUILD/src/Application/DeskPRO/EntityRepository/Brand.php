<?php

namespace Application\DeskPRO\EntityRepository;

class Brand extends AbstractEntityRepository
{
    /**
     * @param array|null $for_ids
     *
     * @return array
     */
    public function getNames(array $for_ids = null)
    {
        $brands = $this->findAll();

        $ret = [];
        foreach ($brands as $brand) {
            if ($for_ids === null || in_array($brand->getId(), $for_ids)) {
                $ret[$brand->getId()] = $brand->getName();
            }
        }

        return $ret;
    }
}
