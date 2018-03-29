<?php

namespace DeskPRO\Bundle\AppBundle\HitTrack\Recorder;

use DeskPRO\Bundle\AppBundle\Entity\HitRecord;

interface HitStorageInterface
{
    /**
     * @param HitRecord $record
     *
     * @return string An ID of some kind (may be empty if storage doesnt support it)
     */
    public function record(HitRecord $record);
}
