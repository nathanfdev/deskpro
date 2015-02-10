<?php
/**************************************************************************\
| DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/  |
| a British company located in London, England.                            |
|                                                                          |
| All source code and content Copyright (c) 2014, DeskPRO Ltd.             |
|                                                                          |
| The license agreement under which this software is released              |
| can be found at https://www.deskpro.com/eula/                            |
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
 */

namespace Application\DeskPRO\Command;

use Application\DeskPRO\App;
use Application\DeskPRO\Email\EmailAccount\OutgoingAccount\PhpMailConfig;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenRandomEmailCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('dp:gen-rand-email');
        $this->addOption('from-email', null, InputOption::VALUE_REQUIRED);
        $this->addOption('reply-to-email', null, InputOption::VALUE_REQUIRED);
        $this->addOption('original-from-email', null, InputOption::VALUE_REQUIRED);
        $this->addOption('to-email', null, InputOption::VALUE_REQUIRED);
        $this->addOption('with-image', null, InputOption::VALUE_NONE);
        $this->addOption('attach', null, InputOption::VALUE_REQUIRED);
        $this->addOption('subject', null, InputOption::VALUE_REQUIRED);
        $this->addOption('fwd-for', null, InputOption::VALUE_REQUIRED);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fwd_for = $input->getOption('fwd-for') ? $input->getOption('fwd-for') : false;
        $subject = $input->getOption('subject') ?: 'Test Email - %TIME%';

        if ($subject == "EMPTY") {
            $subject = "";
        }

        $email_pre = "";

        $email_pre_html = "";
        if ($email_pre) {
            $email_pre_html = "<div>".nl2br($email_pre)."</div>";
        }

        $from_lines = array();
        if ($input->hasOption('from-email')) {
            $from_lines[] = "From: ".$input->getOption('from-email');
        }
        if ($input->hasOption('reply-to-email')) {
            $from_lines[] = "Reply-To: ".$input->getOption('reply-to-email');
        }
        if ($input->hasOption('original-from-email')) {
            $from_lines[] = "X-Original-From: ".$input->getOption('original-from-email');
        }

        $from_lines = implode("\n", $from_lines);

        $fwd_footer      = '';
        $fwd_footer_html = '';
        if ($fwd_for) {
            $fwd_footer      = "\n\n----- Forwarded Message -----\nFrom: $fwd_for\nSubject: $subject\n\nOriginal message from the user\n\n";
            $fwd_footer_html = "<div>".nl2br($fwd_footer)."</div>";
            $subject         = "FW: ".$subject;
        }

        if (!$input->getOption('with-image') && !$input->getOption('attach')) {
            $source = <<<SRC
Date: Mon, 10 Dec 2012 19:15:33 +0000
$from_lines
To: %TO_EMAIL%
Message-ID: <144FD598151749D98C378FCF8B2E03C2@gmail.com>
Subject: $subject
X-Mailer: sparrow 1.6.4 (build 1176)
MIME-Version: 1.0
Content-Type: multipart/alternative; boundary="50c634de_3222e7cd_af2f"

--50c634de_3222e7cd_af2f
Content-Type: text/plain; charset="utf-8"
Content-Transfer-Encoding: 7bit
Content-Disposition: inline

$email_pre
Test Subject - 2012-12-10 19:15:29
$fwd_footer

-- Christopher


--50c634de_3222e7cd_af2f
Content-Type: text/html; charset="utf-8"
Content-Transfer-Encoding: quoted-printable
Content-Disposition: inline

$email_pre_html
<div>Test Message</div>
%MSG_UID%
$fwd_footer_html

--50c634de_3222e7cd_af2f--

SRC;
        } else {
            if ($input->getOption('attach')) {
                $file     = file_get_contents($input->getOption('attach'));
                $filename = basename(realpath($input->getOption('attach')));

                if (!$file) {
                    echo "Invalid attach file\n";

                    return 1;
                }

                $filetype = \Orb\Data\ContentTypes::getContentTypeFromFilename($filename);
                if (!$filetype) {
                    $filetype = 'application/octet-stream';
                }

                if ($filename == 'winmail.dat') {
                    $filetype = 'application/ms-tnef';
                }
            } else {
                $file     = file_get_contents(DP_ROOT.'/../web/images/admin/agent-screen.png');
                $filename = 'agent-screen.png';
                $filetype = 'image/png';
            }

            $file = base64_encode($file);

            $source = <<<SRC
Received: from [172.18.24.247] (iw-01.clients.vorboss.net. [194.8.255.114])
        by mx.google.com with ESMTPS id t17sm17495468wiv.6.2012.12.11.10.40.53
        (version=TLSv1/SSLv3 cipher=OTHER);
        Tue, 11 Dec 2012 10:40:54 -0800 (PST)
Date: Tue, 11 Dec 2012 18:40:52 +0000
$from_lines
To: %TO_EMAIL%
Message-ID: <B5522AAC086547DFB50EDB640A75AE8E@deskpro.com>
Subject: Test Email - %TIME%
MIME-Version: 1.0
Content-Type: multipart/mixed; boundary="50c77e34_725a06fb_dfd0"

--50c77e34_725a06fb_dfd0
Content-Type: multipart/alternative; boundary="50c77e34_1d4ed43b_dfd0"

--50c77e34_1d4ed43b_dfd0
Content-Type: text/plain; charset="utf-8"
Content-Transfer-Encoding: 7bit
Content-Disposition: inline

$email_pre
Test Message
%MSG_UID%
$fwd_footer


--50c77e34_1d4ed43b_dfd0
Content-Type: text/html; charset="utf-8"
Content-Transfer-Encoding: quoted-printable
Content-Disposition: inline

$email_pre_html
<div>Test Message</div>
%MSG_UID%
$fwd_footer_html

--50c77e34_1d4ed43b_dfd0--

--50c77e34_725a06fb_dfd0
Content-Type: $filetype
Content-Transfer-Encoding: base64
Content-Disposition: attachment; filename="$filename"

$file

--50c77e34_725a06fb_dfd0--
SRC;
        }
        $from_email = $input->getOption('from-email');
        $to_email   = $input->getOption('to-email');
        $time       = date('Y-m-d H:i:s');

            $source = str_replace('%FROM_EMAIL%', $from_email, $source);
            $source = str_replace('%TO_EMAIL%', $to_email, $source);
            $source = str_replace('%TIME%', $time, $source);
            $source = str_replace('%MSG_UID%', uniqid('eml-', true), $source);

            echo $source;
        }
    }
