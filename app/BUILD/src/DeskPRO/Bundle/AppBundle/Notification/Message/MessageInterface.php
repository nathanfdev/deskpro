<?php

namespace DeskPRO\Bundle\AppBundle\Notification\Message;

/**
 * Interface MessageInterface.
 */
interface MessageInterface
{
    public function __construct($target, $data, $type);

    public function getTarget();

    public function getData();

    public function getDate();

    public function getType();

    public function getId();
}
