<?php

namespace Application\EmailBundle\Mail\RawMessage\Mail;

use Zend\Mail\Headers as BaseHeaders;
use Zend\Mail\Storage\AbstractStorage;
use Zend\Mail\Storage\Exception\InvalidArgumentException;
use Zend\Mime;

class Part extends \Zend\Mail\Storage\Part
{
    public function __construct(array $params)
    {
        if (isset($params['handler'])) {
            if (!$params['handler'] instanceof AbstractStorage) {
                throw new InvalidArgumentException('handler is not a valid mail handler');
            }
            if (!isset($params['id'])) {
                throw new InvalidArgumentException('need a message id with a handler');
            }

            $this->mail       = $params['handler'];
            $this->messageNum = $params['id'];
        }

        $params['strict'] = isset($params['strict']) ? $params['strict'] : false;

        if (isset($params['raw'])) {
            Decode::splitMessage($params['raw'], $this->headers, $this->content, Mime\Mime::LINEEND, $params['strict']);
        } elseif (isset($params['headers'])) {
            if ($params['headers'] instanceof BaseHeaders) {
                $this->headers = $params['headers'];
            } elseif (is_array($params['headers'])) {
                $this->headers = new Headers();
                $this->headers->addHeaders($params['headers']);
            } else {
                if (empty($params['noToplines'])) {
                    Decode::splitMessage($params['headers'], $this->headers, $this->topLines);
                } else {
                    $this->headers = Headers::fromString($params['headers']);
                }
            }

            if (isset($params['content'])) {
                $this->content = $params['content'];
            }
        }
    }
}
