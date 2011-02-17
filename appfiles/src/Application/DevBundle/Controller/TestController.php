<?php

namespace Application\DevBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use \Application\DeskPRO\App;

class TestController extends Controller
{
    public function indexAction()
    {
		$mailer = App::getMailer();

		App::get('deskpro.core.settings')->setTemporarySettingValues(array('core.use_mail_queue' => 'smart'));
		$mailer->getTransport()->disableQueue();

		$message = new \Orb\Mail\Message();
		$message->enableQueueHint();
		$message->setTo('chroder@gmail.com', 'Christopher Nadeau');
		$message->setFrom('chris.nadeau@deskpro.com');
		$message->setSubject('Testing');
		$message->setBody('Testing 123');

		var_dump($mailer->send($message));

		exit;
    }
}
