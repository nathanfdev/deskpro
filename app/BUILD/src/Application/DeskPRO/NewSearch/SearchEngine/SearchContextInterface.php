<?php

namespace Application\DeskPRO\NewSearch\SearchEngine;

interface SearchContextInterface
{
    /**
     * @return int[]
     */
    public function getArticleCategoryIds();

    /**
     * @return int[]
     */
    public function getDownloadCategoryIds();

    /**
     * @return int[]
     */
    public function getNewsCategoryIds();

    /**
     * @return int[]
     */
    public function getFeedbackCategoryIds();

    /**
     * @return int[]
     */
    public function getGuideIds();

    /**
     * @return \Application\DeskPRO\Entity\Person|null
     */
    public function getPerson();

    /**
     * @return \Application\DeskPRO\Entity\Brand
     */
    public function getBrand();
}
