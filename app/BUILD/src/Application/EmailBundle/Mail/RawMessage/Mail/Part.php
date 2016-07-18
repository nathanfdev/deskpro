<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2016, DeskPRO Ltd.
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

namespace Application\EmailBundle\Mail\RawMessage\Mail;

use Zend\Mail\Headers;
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
            if ($params['headers'] instanceof Headers) {
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
