<?php

namespace DeskPRO\Bundle\BrandBundle\Request;

use Symfony\Component\HttpFoundation\Request;

/**
 * Class OriginalRequestStorage.
 */
class OriginalRequestStorage
{
    /**
     * @var Request
     */
    private $originalRequest;

    /**
     * @param Request $originalRequest
     */
    public function setOriginalRequest(Request $originalRequest)
    {
        $this->originalRequest = $originalRequest;
    }

    /**
     * @return Request
     */
    public function getOriginalRequest()
    {
        return $this->originalRequest;
    }
}
