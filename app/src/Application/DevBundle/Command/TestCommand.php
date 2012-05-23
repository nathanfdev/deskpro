<?php
/**
 * DeskPRO
 *
 * @package DeskPRO
 * @category Commands
 * @copyright Copyright (c) 2010 DeskPRO (http://www.deskpro.com/)
 * @license http://www.deskpro.com/license-agreement DeskPRO License
 * @author Christopher Nadeau <chris.nadeau@deskpro.com>
 */

namespace Application\DevBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

use Application\DeskPRO\App;

use Orb\Util\Arrays;
use Orb\Util\Strings;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Routing\Route;

class TestCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
	protected function configure()
	{
		$this->setDefinition(array(
		))->setName('dpdev:test');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$rename_map = array();

		$files = array(
			'chat.php',
			'defaults.php',
			'downloads.php',
			'email_subjects.php',
			'emails.php',
			'error.php',
			'feedback.php',
			'knowledgebase.php',
			'time.php',
			//'widget.php'
		);

		$list = array(
'user.general.feedback' => 'user.feedback.feedback',
'user.general.knowledgebase' => 'user.knowledgebase.knowledgebase',
'user.general.related_articles' => 'user.knowledgebase.related_articles',
'user.general.related_downloads' => 'user.downloads.related_downloads',
'user.general.related_news' => 'user.news.related_news',
'user.general.type_article' => 'user.knowledgebase.type_article',
'user.general.type_download' => 'user.downloads.type_download',
'user.general.type_news' => 'user.news.type_news',
'user.general.latest_news' => 'user.news.latest_news',
'user.general.news' => 'user.news.news',
'user.general.lost_password_explain' => 'user.profile.lost_password_explain',
'user.general.lost_password_sent' => 'user.profile.lost_password_sent',
'user.general.posted_date' => 'user.news.posted_date',
		);

		/*
		foreach ($files as $f) {
			$f1 = DP_ROOT.'/languages/user/' . $f;
			$f2 = DP_ROOT.'/languages2/user/' . $f;

			$arr1 = require($f1);
			$arr2 = require($f2);

			$keys1 = array_keys($arr1);
			$keys2 = array_keys($arr2);

			foreach ($keys1 as $index => $key1) {

				if (!isset($keys2[$index])) {
					echo "Missing key:  $key1\n";
					echo "------------------------------------------\n";
					continue;
				}

				$key2 = $keys2[$index];

				if ($key1 == $key2) {
					continue;
				}

				$phrase1 = $arr1[$key1];
				$phrase2 = $arr2[$key2];

				if ($phrase1 != $phrase2) {

					$phrase1 = substr($phrase1, 0, 50);
					$phrase2 = substr($phrase2, 0, 50);

					echo "Old:  $key1   $phrase1\n";
					echo "New:  $key2   $phrase2\n";
					echo "------------------------------------------\n";
				}

				$list[$key1] = $key2;
			}
		}

		exit;
		*/
		$files = \Symfony\Component\Finder\Finder::create()->files()->in(array(
			DP_ROOT.'/languages/user',
			DP_ROOT.'/src/Application/UserBundle',
			DP_ROOT.'/src/Application/DeskPRO',
		));

		foreach ($files as $f) {
			echo "{$f->getRealPath()} ... ";
			$content = file_get_contents($f->getRealPath());
			$new_content = $content;
			foreach ($list as $old_key => $new_key) {
				$new_content = str_replace($old_key, $new_key, $new_content);
			}
			if ($content !== $new_content) {
				file_put_contents($f->getRealPath(), $new_content);
			}
			echo " k\n";
		}
	}
}
