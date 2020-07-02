<?php

namespace DeskPRO\Bundle\DevBundle\Template;

use DpRun\DpEnv;
use DpSys\Boot\BootTask\HttpKernelBootTask;
use DpSys\Kernel\BaseKernel;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\TwigTest;

class TwigExtensionScanner
{
    const KERNEL_INSTANCE_IDS = [
        'messenger',
        'apiv2',
        'user',
        'dp',
    ];

    const TWIG_SERVICE_IDS = [
        'twig',
        'templating.email.twig',
        'templating.new_email.twig',
    ];

    /**
     * @return array[]
     * @throws \Exception
     */
    public function getAllExtensionTokenNames()
    {
        $filters   = [];
        $functions = [];

        foreach (self::KERNEL_INSTANCE_IDS as $id) {
            $container = $this->getContainerForKernel($id);

            foreach (self::TWIG_SERVICE_IDS as $twigServiceId) {
                if (!$container->has($twigServiceId)) {
                    continue;
                }

                $tokenNames = $this->getTwigExtensionTokenShortNames($container->get($twigServiceId));

                $filters   = array_merge($filters, $tokenNames['filters']);
                $functions = array_merge($functions, $tokenNames['functions']);
            }
        }

        return [
            'filters' => array_values($filters),
            'functions' => array_values($functions),
        ];
    }

    /**
     * @param Environment $twig
     * @return array[]
     * @throws \Exception
     */
    private function getTwigExtensionTokenShortNames(Environment $twig)
    {
        $filters   = [];
        $functions = [];
        $tests     = [];

        foreach ($twig->getExtensions() as $extension) {
            foreach ($extension->getFilters() as $name => $filter) {
                if ($filter instanceof TwigFilter) {
                    $filters[$filter->getName()] = $filter->getName();
                } elseif ($filter instanceof \Twig_Filter_Method) {
                    $filters[$name] = $name;
                } else {
                    throw new \Exception("Failed to determine filter name from ".get_class($filter));
                }
            }

            foreach ($extension->getFunctions() as $name => $function) {
                if ($function instanceof TwigFunction) {
                    $functions[$function->getName()] = $function->getName();
                } else {
                    throw new \Exception("Failed to determine function name from ".get_class($function));
                }
            }

            foreach ($extension->getTests() as $name => $test) {
                if ($test instanceof TwigTest) {
                    $tests[$test->getName()] = $test->getName();
                } else {
                    throw new \Exception("Failed to determine test name from ".get_class($test));
                }
            }
        }

        return [
            'filters'   => $filters,
            'functions' => $functions,
            'tests'     => $tests,
        ];
    }

    /**
     * @param string $instanceId
     * @return ContainerInterface|null
     */
    private function getContainerForKernel($instanceId)
    {
        $env = new DpEnv(__DIR__.'/../../../../../../..');

        /** @var BaseKernel $kernel */
        list ('http_kernel' => $kernel) = (new HttpKernelBootTask())
            ->run($env, ['interface_id' => $instanceId, 'request' => new Request()])
        ;

        $kernel->boot();

        return $kernel->getContainer();
    }
}
