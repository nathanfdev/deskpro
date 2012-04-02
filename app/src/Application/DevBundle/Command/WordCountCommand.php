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

class WordCountCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
	protected function configure()
	{
		$this->setDefinition(array(
		))->setName('dpdev:word-count');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{

        $directories = array(
            array('user', 'User Interface'),
            array('agent', 'Agent Interface'),
            array('admin', 'Admin Interface'),
			array('billing', 'Billing System'),
			array('deskpro', 'DeskPRO'),
			array('report', 'Reports')
        );

		$t_wordcount = 0;
		$t_keycount = 0;

        foreach ($directories AS $interface) {

            $keycount = 0;
            $wordcount = 0;

            $dir = DP_ROOT . '/languages/DeskPRO/' . $interface['0'];

            if ($handle = opendir($dir)) {
                while (false !== ($filename = readdir($handle))) {
                    if ($filename != "." && $filename != "..") {

                        $words = include($dir . '/' . $filename);
                        $keycount += count($words);
                        foreach ($words AS $key => $var) {
                            $wordcount += str_word_count($var);
                        }
                    }
                }

                closedir($handle);

                echo $interface['1'] . " :: $wordcount words in $keycount phrases\n";
				$t_wordcount += $wordcount;
				$t_keycount += $keycount;

            }
        }

		
		echo "\n\nTotal :: $t_wordcount words in $t_keycount phrases\n";

	}
}