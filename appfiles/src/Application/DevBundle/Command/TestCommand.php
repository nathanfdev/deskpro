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
		$time = microtime(true);

		$filepath = DP_ROOT . '/src/Application/ApiBundle/Resources/config/routing.yml';
		$outpath = DP_ROOT . '/src/Application/ApiBundle/Resources/config/routing.php';
		$config = Yaml::parse($filepath);

		//print_r($config);exit;

		$php = array();
		$php[] = "<?php\n\n";
		$php[] = "use Symfony\\Component\\Routing\\RouteCollection;\n";
		$php[] = "use Symfony\\Component\\Routing\\Route;\n\n";

		$php[] = "\$collection = new RouteCollection();\n\n";

		foreach ($config as $routename => $info) {
			$php[] = "\$collection->add('$routename', new Route(\n\t'{$info['pattern']}',\n";

			$php[] = "\tarray(";
			$subphp = array();
			foreach ($info['defaults'] as $k => $v) {
				$subphp[] = "'$k' => " . var_export($v, true);
			}
			$subphp = implode(", ", $subphp);
			$php[] = $subphp;

			$php[] = "),\n\tarray(";

			if (!empty($info['requirements'])) {
				$subphp = array();
				foreach ($info['requirements'] as $k => $v) {
					$subphp[] = "'$k' => " . var_export($v, true);
				}
				$subphp = implode(", ", $subphp);
				$php[] = $subphp;
			}

			$php[] = "),\n";

			$php[] = "\tarray(";
			if (!empty($info['options'])) {
				$subphp = array();
				foreach ($info['options'] as $k => $v) {
					$subphp[] = "'$k' => " . var_export($v, true);
				}
				$subphp = implode(", ", $subphp);
				$php[] = $subphp;
			}
			$php[] = ")\n";
			$php[] = "));\n";

			$php[] = "\n";
		}

		$php[] = "\nreturn \$collection;\n";

		$php = implode('', $php);

		@unlink($outpath);
		file_put_contents($outpath, $php);

		$end = microtime(true);

		printf("Took %.f s", $end-$time);
	}
}
