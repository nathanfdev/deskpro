<?php

namespace Application\DeskPRO\NewSearch\SearchEngine;

interface SearchContextInterface
{
    const SEARCH_BY_ID = 'search_id';

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

    /**
     * @param string $name
     * @param mixed  $value
     *
     * @return $this
     */
    public function setOption($name, $value);

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function getOption($name);

    /**
     * @param array $allowedFields
     *
     * @return $this
     */
    public function setAllowedFields($allowedFields);

    /**
     * @return array
     */
    public function getAllowedFields();
}
