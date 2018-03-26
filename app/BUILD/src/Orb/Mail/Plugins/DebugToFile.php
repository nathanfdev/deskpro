<?php

/**
 * Orb.
 */

namespace Orb\Mail\Plugins;

/**
 * Completely turns off email sending.
 */
class DebugToFile implements \Swift_Events_SendListener
{
    /** @var string */
    protected $filepath;
    /** @var bool */
    protected $cancel_send = false;
    /** @var string|bool */
    protected $info_file_path = false;

    public function __construct($filepath, $cancel_send = false)
    {
        $this->filepath    = rtrim($filepath, '/\\');
        $this->cancel_send = $cancel_send;
    }

    public function sendPerformed(\Swift_Events_SendEvent $evt)
    {
    }

    public function beforeSendPerformed(\Swift_Events_SendEvent $evt)
    {
        if ($this->cancel_send) {
            $evt->cancelBubble();
        }

        $message = $evt->getMessage();
        $name    = time().mt_rand(1000, 9999).'_'.preg_replace('#[^a-zA-Z0-9]#', '-', substr($message->getSubject(), 0, 50));
        $name    = preg_replace('#-{,2}#', '-', $name);

        $path = $this->filepath.DIRECTORY_SEPARATOR.$name.'.txt';

        file_put_contents($path, $message->toString());

        if ($this->info_file_path) {
            if ($tos = $message->getTo()) {
                $tos = $tos;
            } else {
                $tos = [];
            }

            if ($ccs = $message->getCc()) {
                $ccs = $ccs;
            } else {
                $ccs = [];
            }

            if ($from = $message->getFrom()) {
                $from = $from;
            } else {
                $from = [];
            }

            $domain = !empty($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null;
            if (defined('DPC_SITE_DOMAIN')) {
                $domain = DPC_SITE_DOMAIN;
            }

            file_put_contents($this->info_file_path.DIRECTORY_SEPARATOR.$name.'.json', json_encode([
                'date'       => date('Y-m-d H:i:s'),
                'tos'        => $tos,
                'ccs'        => $ccs,
                'from'       => $from,
                'subject'    => $message->getSubject(),
                'domain'     => $domain,
                'store_path' => $path,
            ]));
        }
    }

    public function setInfoFilePath($path)
    {
        $this->info_file_path = $path;
    }
}
