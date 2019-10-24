<?php

namespace DeskPRO\Bundle\AppBundle\Entity;

interface HasIconProperty
{
    /**
     * @return IconProperty
     */
    public function getIcon();

    /**
     * @param IconProperty $iconProperty
     *
     * @return mixed
     */
    public function setIcon($iconProperty);
}
