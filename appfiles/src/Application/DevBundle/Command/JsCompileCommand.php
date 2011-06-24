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
class JsCompileCommand extends \Symfony\Bundle\FrameworkBundle\Command\Command
{
	protected function configure()
	{
		$this->setDefinition(array(
			
		))->setName('dpdev:js-compile');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$packs = array(
			array(
				'mode' => 'yui',
				'out' => 'agent-vendors.js',
				'files' => array(
					'vendor/jquery/jquery.min.js',
					'vendor/jquery/jquery-ui/jquery-ui.min.js',
					'vendor/jquery/jquery-tmpl/jquery.tmpl.min.js',
					
					'vendor/jquery/jquery.cookie.js',
					'vendor/jquery/jquery.form.js',
					'vendor/jquery/jquery.form.js',
					'vendor/jquery/jquery.layout.min.js',
					'vendor/jquery/jquery.localscroll.js',
					'vendor/jquery/jquery.mousewheel.js',
					'vendor/jquery/jquery.scrollTo.js',
					'vendor/jquery/jquery.sizes.min.js',
					'vendor/jquery/jquery.timeago.js',
					'vendor/jquery/jquery.tinyscrollbar.js',
					'vendor/jquery/mwheelIntent.js',

					'vendor/jquery/colorbox/jquery.colorbox-min.js',
					
					'vendor/jquery/fileupload/jquery.fileupload.js',
					'vendor/jquery/fileupload/jquery.fileupload-ui.js',

					'vendor/jquery/jcrop/js/jquery.Jcrop.min.js',

					'vendor/jquery/tag-it/tag-it.js',

					'vendor/jquery/tipped/js/bridge/bridge.js',
					'vendor/jquery/tipped/js/bridge/adapters/shared.js',
					'vendor/jquery/tipped/js/bridge/adapters/jquery.js',
					'vendor/jquery/tipped/js/excanvas/excanvas.js',
					'vendor/jquery/tipped/js/spinners/spinners.js',
					'vendor/jquery/tipped/js/tipped/tipped.js',

					'vendor/mootools/mootools-core.min.js',
					'vendor/modernizr.min.js',
				)
			)
		);

		$static_path = realpath(DP_ROOT . '/../static/');
		$output->writeln("Static path: " . $static_path);

		foreach ($packs as $pack) {
			$this->compilePack($output, $pack);
		}
	}

	public function compilePack(OutputInterface $output, array $pack)
	{
		$output->writeln("<info>Building pack {$pack['out']}</info>");

		$static_path = realpath(DP_ROOT . '/../static') . '/';
		$filepath = $static_path . 'build/' . $pack['out'];
		$filepath_tmp = $static_path . 'build/' . $pack['out'] . '.build';

		echo "Making combined file\n";
		$fp = fopen($filepath_tmp, 'w');
		if (!$fp) {
			$output->writeln("<error>Could not create build file</error>");
			return;
		}

		foreach ($pack['files'] as $f) {
			if (!file_exists($static_path . $f)) {
				$output->writeln("<error>$f does not exist</error>");
				return;
			}
			$content = file_get_contents($static_path . $f) . "\n\n\n\n\n";
			$content = preg_replace('#/\*\*(.*?)\*/#s', '', $content);
			$content = preg_replace('#/\*!(.*?)\*/#s', '', $content);
			fwrite($fp, $content);

			echo ".";
		}

		echo "Done\n\n";

		if ($pack['mode'] == 'yui') {

			$type = 'js';
			if (strpos($filepath, '.css') !== false) {
				$type = 'css';
			}

			$cmd = App::getConfig('debug.yui_compressor_cmd');
			$cmd .= ' -v  --line-break 120 --type '.$type.' -o ' . $filepath . ' ' . $filepath_tmp . '';

			echo "Running YUI compressor: $cmd";

			$ret = null;
			passthru($cmd, $ret);

			if ($ret == 0) {
				$output->writeln('<info>Success</info>');

				echo "Original filesize: " . filesize($filepath_tmp) . "\n";
				echo "Minified: " . filesize($filepath) . "\n";

				unlink($filepath_tmp);
			} else {
				$output->writeln('<error>Exited with an error status, something went wrong</error>');
			}
		} else {
			rename($filepath_tmp, $filepath);
			$output->writeln('<info>Success</info>');
		}
	}
}