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
		'user.portal.everything' => 'user.portal.search-everything',
		'user.portal.is_search_relevant' => 'user.portal.search_unhelpful',
		'user.portal.online_staff' => 'user.portal.online-staff',
		'user.portal.our_staff' => 'user.portal.our-staff',
		'user.portal.searchbox_show_label_prefox' => 'user.portal.search_label-search_with',
		'user.portal.searchbox_show_type_prefix' => 'search_label-search_show',
		'user.portal.twitter_user_link' => 'user.portal.twitter-user-link',
		'user.portal.twitter_view_more' => 'user.portal.twitter-view-more',
		'user.portal.view_more_search_results' => 'user.portal.search_more-results',
		'user.portal.we_are_here_to_help' => 'user.portal.sidebar_staff-description',
		'user.portal.welcome_back_guest' => 'user.portal.welcome-back-guest',
		'user.portal.welcome_back_x' => 'user.portal.sidebar_welcome-user',
		'user.portal.x_search_results' => 'user.portal.search_result-count',
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
