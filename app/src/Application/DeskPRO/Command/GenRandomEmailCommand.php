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
 */

namespace Application\DeskPRO\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

use Application\DeskPRO\App;
use Application\DeskPRO\Entity;

class GenRandomEmailCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
	protected function configure()
	{
		$this->setName('dp:gen-rand-email');
		$this->addOption('from-email', null, InputOption::VALUE_REQUIRED);
		$this->addOption('to-email', null, InputOption::VALUE_REQUIRED);
		$this->addOption('real-send', null, InputOption::VALUE_NONE);
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$source = <<<SRC
Date: Mon, 10 Dec 2012 19:15:33 +0000
From: %FROM_EMAIL%
To: %TO_EMAIL%
Message-ID: <144FD598151749D98C378FCF8B2E03C2@gmail.com>
Subject: Test Email - %TIME%
X-Mailer: sparrow 1.6.4 (build 1176)
MIME-Version: 1.0
Content-Type: multipart/alternative; boundary="50c634de_3222e7cd_af2f"

--50c634de_3222e7cd_af2f
Content-Type: text/plain; charset="utf-8"
Content-Transfer-Encoding: 7bit
Content-Disposition: inline

Test Subject - 2012-12-10 19:15:29

-- Christopher


--50c634de_3222e7cd_af2f
Content-Type: text/html; charset="utf-8"
Content-Transfer-Encoding: quoted-printable
Content-Disposition: inline


                <div>Test Message</div>
                %MSG_UID%

--50c634de_3222e7cd_af2f--

SRC;

		$from_email = $input->getOption('from-email');
		$to_email   = $input->getOption('to-email');
		$time       = date('Y-m-d H:i:s');

		if ($input->getOption('real-send')) {
			$message = App::getMailer()->createMessage();
			$message->setTo($to_email);
			$message->setFrom($from_email);
			$message->setSubject('Test Email - ' . $time);
			$message->getBody("Test Message\n\n" . uniqid('eml-', true));

			$tr = new \Application\DeskPRO\Entity\EmailTransport();
			$tr->match_type = 'all';
			$tr->title = 'contact';
			$tr->transport_type = 'mail';

			$message->setForceTransport($tr);

			App::getMailer()->send($message);

			echo "Message Sent\n";
		} else {
			$source = str_replace('%FROM_EMAIL%', $from_email, $source);
			$source = str_replace('%TO_EMAIL%', $to_email, $source);
			$source = str_replace('%TIME%', $time, $source);
			$source = str_replace('%MSG_UID%', uniqid('eml-', true), $source);

			echo $source;
		}
	}
}