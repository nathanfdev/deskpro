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

use Orb\Log\Logger;
use Orb\Log\Writer\ArrayWriter;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\Output;

use Application\DeskPRO\App;

use Orb\Util\Arrays;
use Orb\Util\Strings;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Routing\Route;

class TestCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
	protected function configure()
	{
		$this->setDefinition(array(
		))->setName('dp:test');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		//$mode = "copy";
		$mode = "rename";

		$lang_path = '/deskpro/www/app/languages/default/adm';
		$interface_path = '/deskpro/www/app/src/Application/AdminInterfaceBundle/Resources';
		$interface_files = iterator_to_array(Finder::create()->files()->name('*.twig')->in($interface_path));

		if ($mode == "copy") {
			foreach (array('departments.php', 'general.php', 'tickets.php') as $lang_file) {
				$lang_file_path = $lang_path."/$lang_file";
				$lang_outfile_path = $lang_path."/new.$lang_file";
				$phrases = require($lang_file_path);

				$out_phrases = array();
				foreach ($interface_files as $f) {
					$content = file_get_contents($f->getRealPath());

					foreach ($phrases as $id => $name) {
						if (isset($out_phrases[$id])) continue;
						if (strpos($content, $id) !== false) {
							$out_phrases[$id] = $name;
						}
					}
				}

				ksort($out_phrases, \SORT_STRING);
				$out_phrases = Arrays::prettyDump($out_phrases);

				file_put_contents($lang_outfile_path, "<?php return " . $out_phrases . ";");
			}
		} elseif ($mode == "rename") {
			$phrase_ids = array();
			foreach (array('departments.php', 'general.php', 'tickets.php') as $lang_file) {
				$file_phrases = require($lang_path."/$lang_file");
				$phrase_ids = array_merge($phrase_ids, array_keys($file_phrases));
			}

			$find_arr = array();
			$repl_arr = array();
			foreach ($phrase_ids as $k) {
				$find_arr[] = preg_replace('#^adm\.#', 'admin.', $k);
				$repl_arr[] = $k;
			}

			foreach ($interface_files as $f) {
				$old_content = file_get_contents($f->getRealPath());
				$new_content = str_replace($find_arr, $repl_arr, $old_content);

				if ($old_content != $new_content) {
					file_put_contents($f->getRealPath(), $new_content);
				}
			}
		}
	}
}
