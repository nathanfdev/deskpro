<?php

namespace DeskPRO\Bundle\AppStoreBundle\Domain;

interface ApplicationFinder
{
    /**
     * @return Application[]
     */
    public function findAll();

    /**
     * @param array $idList
     *
     * @return Application[]
     */
    public function findAllById($idList);

    /**
     * Finds one application by reference.
     *
     * @param string $name
     *
     * @return Application
     */
    public function findByName($name);

    /**
     * Finds one application by unique system identifier.
     *
     * @param string $id
     *
     * @return Application
     */
    public function findByInstanceId($id);
}
