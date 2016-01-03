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
