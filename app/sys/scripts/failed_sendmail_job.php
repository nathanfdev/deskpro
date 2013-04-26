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

namespace DeskPRO\Kernel;

if (!defined('DP_ROOT')) exit('No access');

use Application\DeskPRO\Entity\SendmailQueue;
use Orb\Util\Arrays;
use Orb\Util\Util;
use Orb\Util\Web;

require_once DP_ROOT.'/sys/serve_abstract.php';

/**
 * Take a request that saves a failed email
 */
class FailedSendmailJob extends LoaderAbstract
{
	public function runAction()
	{
		$auth = dp_get_config('set_failed_sendmail_job_auth');
		if (!$auth || !isset($_GET[$auth])) {
			echo 'DP_FAIL_AUTH';
			exit;
		}

		if (!isset($_FILES['mailfile']) || !empty($_FILES['mailfile']['error']) || empty($_FILES['mailfile']['tmp_name'])) {
			echo "DP_MAILFILE_INVALID";
			exit(1);
		}

		// email data
		if (!isset($_POST['data'])) {
			echo 'DP_MISSING_DATA';
			exit;
		}

		$data = @json_decode($_POST['data'], true);

		if (!$data) {
			echo 'DP_INVALID_DATA';
			exit;
		}

		// job data
		if (!isset($_POST['job_data'])) {
			echo 'DP_MISSING_JOBDATA';
			exit;
		}

		$job_data = @json_decode($_POST['job_data'], true);

		if (!$job_data) {
			echo 'DP_INVALID_JOBDATA';
			exit;
		}

		#------------------------------
		# Save the job message file
		#------------------------------

		$tmppath = tempnam(sys_get_temp_dir(), 'dpe');
		$fp = fopen($tmppath, 'w');

		$header_string = Arrays::implodeTemplate($job_data, "{KEY}: {VAL}\n");
		fwrite($fp, $header_string . "\n" . json_encode($data) . "\n\n");

		$up_fp = fopen($_FILES['mailfile']['tmp_name'], 'r');
		while (!feof($up_fp)) {
			fwrite($fp, fread(1024, $up_fp));
		}
		fclose($up_fp);

		fclose($fp);

		#------------------------------
		# Save it as a failed email
		#------------------------------

		$container = $this->bootFullSystem();
		$blob = $container->getBlobStorage()->createBlobRecordFromFile($tmppath, 'sendmail.job', 'plain/text');

		$email = new SendmailQueue();
		$email->blob         = $blob;
		$email->subject      = $data['subject'];
		$email->to_address   = array_merge($data['to_addresses'], $data['cc_addresses'], $data['bcc_addresses']);
		$email->from_address = $data['from_addresses'];
		$email->attempts     = 4;

		$container->getEm()->persist($email);
		$container->getEm()->flush($email);

		echo "DP_ACCEPT: {$email->id}";
	}
}

$x = new FailedSendmailJob();
$x->run();