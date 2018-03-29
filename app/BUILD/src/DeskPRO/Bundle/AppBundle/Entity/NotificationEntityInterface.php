<?php

namespace DeskPRO\Bundle\AppBundle\Entity;

interface NotificationEntityInterface
{
    /**
     * @return mixed
     */
    public function getTargetId();

    /**
     * @param mixed $target_id
     *
     * @return NotificationEntityInterface
     */
    public function setTargetId($target_id);

    /**
     * @return string
     */
    public function getUuid();

    /**
     * @param string $uuid
     *
     * @return NotificationEntityInterface
     */
    public function setUuid($uuid);

    /**
     * @return \DateTime
     */
    public function getDateCreated();

    /**
     * @param \DateTime $date_created
     *
     * @return NotificationEntityInterface
     */
    public function setDateCreated($date_created);

    /**
     * @return array
     */
    public function getData();

    /**
     * @param array $data
     *
     * @return NotificationEntityInterface
     */
    public function setData($data);

    /**
     * @return string
     */
    public function getType();

    /**
     * @param string $type
     *
     * @return NotificationEntityInterface
     */
    public function setType($type);
}
