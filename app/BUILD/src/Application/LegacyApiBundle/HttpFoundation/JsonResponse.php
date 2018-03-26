<?php

namespace Application\LegacyApiBundle\HttpFoundation;

use Application\DeskPRO\Util;
use Symfony\Component\HttpFoundation\Response;

class JsonResponse extends Response
{
    /** @var array|null */
    protected $data;

    public function __toString()
    {
        if (false === $res = Util::jsonEncode($this->data)) {
            throw new \Exception('Can\t encode to JSON: '.json_last_error_msg()."\n".print_r($this->data, 1));
        }

        return $res;
    }

    public function getData()
    {
        return $this->data;
    }

    public function setContent($content)
    {
        if (is_array($content)) {
            $this->data = $content;
            $content    = $this->__toString();
        }

        parent::setContent($content);
    }
}
