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


/**
 * dpdev:compile-js
 *
 * Compiles and minifies JS source files.
 *
 * NOTE: This command assumes default file structure, where assets are stored in
 * /static
 */
class JsCompileCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
	protected function configure()
	{
		$this->setDefinition(array(
			new InputArgument('pack', InputArgument::REQUIRED, 'The pack to compile. Example: agent.vendors'),
		))->setName('dpdev:js-compile');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$pack_name = $input->getArgument('pack');
		if (!$pack_name) {
			$output->writeln("<error>Specify a pack name</error>");
			return;
		}

		$packs = App::getConfig($pack_name, null, 'js-sources');
		if (!$packs) {
			$output->writeln("<error>Invalid pack name</error>");
			return;
		}

		if ($pack_name != 'agent') {
			$packs = array($pack_name => $packs);
		}

		$errors = array();

		foreach ($packs as $pack_name => $pack) {
			$output->writeln("<info>Building pack {$pack_name}: {$pack['out']}</info>");

			$static_path = realpath(DP_ROOT . '/../static') . '/';
			$filepath = $static_path . 'build/' . $pack['out'];
			$filepath_tmp = $static_path . 'build/' . $pack['out'] . '.build';

			echo "Making combined file\n";
			$fp = fopen($filepath_tmp, 'w');
			if (!$fp) {
				$errors[] = "<error>$pack_name: Could not create build file</error>";
				continue;
			}

			foreach ($pack['files'] as $f) {
				if (!file_exists($static_path . $f)) {
					$errors[] = "<error>$pack_name: $f does not exist</error>";
					continue;
				}
				$content = file_get_contents($static_path . $f) . "\n\n\n\n\n";
				$content = preg_replace('#/\*\*(.*?)\*/#s', '', $content);
				$content = preg_replace('#/\*!(.*?)\*/#s', '', $content);
				fwrite($fp, $content);

				echo ".";
			}

			echo "Done\n\n";

			if ($pack['mode'] == 'yui' OR $pack['mode'] == 'yui-plain') {

				$type = 'js';
				if (strpos($filepath, '.css') !== false) {
					$type = 'css';
				}

				$cmd = App::getConfig('debug.yui_compressor_cmd');

				if ($pack['mode'] == 'yui-plain') {
					$cmd .= ' -v --nomunge --preserve-semi --line-break 120 --type '.$type.' -o ' . $filepath . ' ' . $filepath_tmp . '';
				} else {
					$cmd .= ' -v --line-break 120 --type '.$type.' -o ' . $filepath . ' ' . $filepath_tmp . '';
				}

				echo "Running YUI compressor: $cmd";

				$ret = null;
				passthru($cmd, $ret);

				if ($ret == 0) {
					$output->writeln('<info>Success</info>');

					echo "Original filesize: " . filesize($filepath_tmp) . "\n";
					echo "Minified: " . filesize($filepath) . "\n";

					unlink($filepath_tmp);
				} else {
					$errors[] = "$pack_name: <error>Exited with an error status, something went wrong</error>";
				}
			} else {
				rename($filepath_tmp, $filepath);
				$output->writeln('<info>Success</info>');
			}
		}

		if ($errors) {
			foreach ($errors as $e) {
				$output->writeln($e);
			}
		}
	}
}