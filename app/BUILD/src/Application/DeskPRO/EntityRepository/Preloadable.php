<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\EntityRepository;

interface Preloadable
{
    /**
     * Preloads entities.
     */
    public function preload();
}
