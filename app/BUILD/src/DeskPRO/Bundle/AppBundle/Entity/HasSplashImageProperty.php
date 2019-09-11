<?php

namespace DeskPRO\Bundle\AppBundle\Entity;

interface HasSplashImageProperty
{
    /**
     * @return SplashImageProperty
     */
    public function getSplashImage();

    /**
     * @param SplashImageProperty $splashImageProperty
     *
     * @return mixed
     */
    public function setSplashImage($splashImageProperty);
}
