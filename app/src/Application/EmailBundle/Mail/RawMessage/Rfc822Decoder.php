<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. http://www.deskpro.com/   |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2012, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at http://www.deskpro.com/license                           |
|                                                                          |
| By using this software, you acknowledge having read the license          |
| and agree to be bound thereby.                                           |
|                                                                          |
| Please note that DeskPRO is not free software. We release the full       |
| source code for our software because we trust our users to pay us for    |
| the huge investment in time and energy that has gone into both creating  |
| this software and supporting our customers. By providing the source code |
| we preserve our customers' ability to modify, audit and learn from our   |
| work. We have been developing DeskPRO since 2001, please help us make it |
| another decade.                                                          |
|                                                                          |
| Like the work you see? Think you could make it better? We are always     |
| looking for great developers to join us: http://www.deskpro.com/jobs/    |
|                                                                          |
| ~ Thanks, Everyone at Team DeskPRO                                       |
\**************************************************************************/

/**
 * DeskPRO
 *
 * @package DeskPRO
 * @subpackage
 */

namespace Application\EmailBundle\Mail\RawMessage;

class Rfc822Decoder
{
    public function createRawMessage($raw_fp)
    {
        $message = new \Zend\Mail\Storage\Part(array(
            'raw' => stream_get_contents($raw_fp),
        ));

        $headers = array();
        $ignore_map = array(
            'subject' => true,
            'from' => true,
            'cc' => true,
        );
        foreach ($message->getHeaders()->toArray() as $name => $value) {
            if (isset($ignore_map[strtolower($name)])) {
                continue;
            }

            if (!isset($headers[$name])) {
                $headers[$name] = array();
            }

            if (is_array($value)) {
                $headers[$name] = array_merge($headers[$name], $value);
            } else {
                $headers[$name][] = $value;
            }
        }

        foreach ($message as $part) {
            if ($part->hasChildren()) {
                foreach ($part->getChildren() as $child) {
                    $h = $child->getHeader('Content-Type');
                    if ($h->getType() == 'text/plain' || $h->getType() == 'text/html') {

                    }

                    print_r($h->getType());
                }
            } else {
                $h = $part->getHeader('Content-Type');
                print_r($h->getType());
            }


            echo "\n";
            echo "\n";
            echo "\n";
        }

        //print_r($message->getPart(2));

    }
}