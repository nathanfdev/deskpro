<?php

namespace DeskPRO\Bundle\AppBundle\Command\Utility;

use Application\EmailBundle\Twig\PreProcessor\EmailPreProcessor as LegacyEmailPreProcessor;
use DeskPRO\Bundle\SendmailBundle\Twig\PreProcessor\EmailPreProcessor;
use DpSys\Kernel\PortalKernel;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;

class RecompileTemplatesCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('dp:utility:recompile-templates')
            ->setDescription('Re-compiles templates in the database')
            ->addArgument('type', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'The type of templates to re-compile. Defaults to all. Types: portal, email', ['portal', 'email', 'legacy-email'])
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $types         = $input->getArgument('type');
        $currentKernel = $this->getContainer()->get('kernel');

        foreach ($types as $type) {
            switch ($type) {
                case 'portal':
                    // Need to use the portal kernel for Portal templates,
                    // so if the current kernel isnt PortalKernel, we need to re-exec
                    // ourselves with the --kernel flag
                    if (!($currentKernel instanceof PortalKernel)) {
                        $ret = $this->execSelf('portal');
                        if ($ret) {
                            return $ret;
                        }
                    } else {
                        $this->recompilePortalTemplates($input, $output);
                    }

                    break;
                case 'email':
                    $this->recompileEmailTemplates($input, $output);

                    break;
                case 'legacy-email':
                    $this->recompileLegacyEmailTemplates($input, $output);

                    break;
                default:
                    $output->writeln("<error>Invalid type: $type</error>");

                    return 1;
            }
        }

        return 0;
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \Exception
     */
    private function recompileEmailTemplates(InputInterface $input, OutputInterface $output)
    {
        $db     = $this->getContainer()->get('database_connection');
        $limit  = 100;
        $offset = 0;

        do {
            $this->clearTwig();
            $twig = $this->getContainer()->get('templating.new_email.twig');

            $templates = $db->fetchAll("
                SELECT *
                FROM templates
                WHERE name LIKE 'SendmailBundle:%'
                ORDER BY id ASC
                LIMIT $limit
                OFFSET $offset
            ");

            if (count($templates) > 0) {
                $output->writeln(sprintf('Re-compiling %d email templates...', count($templates)));
            }

            foreach ($templates as $tpl) {
                $output->write(sprintf('  Compiling %d: %s ... ', $tpl['id'], $tpl['name']));

                try {
                    $proc     = new EmailPreProcessor();
                    $code     = $proc->process($tpl['template_code'], $tpl['name']);
                    $compiled = $twig->compileSource($code, $tpl['name']);

                    $db->update('templates', ['template_compiled' => $compiled], ['id' => $tpl['id']]);

                    $output->writeln('OK');
                } catch (\Exception $e) {
                    $output->writeln('ERROR: '.$e->getMessage());

                    try {
                        $db->delete('templates', ['id' => $tpl['id']]);
                    } catch (\Exception $e) {
                        $output->writeln('Failed to backup template!');

                        throw $e;
                    }
                }
            }

            $offset += $limit;
        } while (count($templates) > 0);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \Exception
     */
    private function recompileLegacyEmailTemplates(InputInterface $input, OutputInterface $output)
    {
        $db     = $this->getContainer()->get('database_connection');
        $limit  = 100;
        $offset = 0;

        do {
            $this->clearTwig();
            $twig = $this->getContainer()->get('templating.email.twig');

            $templates = $db->fetchAll("
                SELECT *
                FROM templates
                WHERE name LIKE 'DeskPRO:emails%' OR name LIKE 'DeskPRO:custom_emails%'
                ORDER BY id ASC
                LIMIT $limit
                OFFSET $offset
            ");

            if (count($templates) > 0) {
                $output->writeln(sprintf('Re-compiling %d legacy email templates...', count($templates)));
            }

            foreach ($templates as $tpl) {
                $output->write(sprintf('  Compiling %d: %s ... ', $tpl['id'], $tpl['name']));

                try {
                    $proc     = new LegacyEmailPreProcessor();
                    $code     = $proc->process($tpl['template_code'], $tpl['name']);
                    $compiled = $twig->compileSource($code, $tpl['name']);

                    $db->update('templates', ['template_compiled' => $compiled], ['id' => $tpl['id']]);

                    $output->writeln('OK');
                } catch (\Exception $e) {
                    $output->writeln('ERROR: '.$e->getMessage());

                    try {
                        $db->delete('templates', ['id' => $tpl['id']]);
                    } catch (\Exception $e) {
                        $output->writeln('Failed to backup template!');

                        throw $e;
                    }
                }
            }

            $offset += $limit;
        } while (count($templates) > 0);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \Exception
     */
    private function recompilePortalTemplates(InputInterface $input, OutputInterface $output)
    {
        $db     = $this->getContainer()->get('database_connection');
        $limit  = 100;
        $offset = 0;

        do {
            $this->clearTwig();
            $twig = $this->getContainer()->get('twig');

            $templates = $db->fetchAll("
                SELECT *
                FROM templates
                WHERE name LIKE 'Theme:%'
                ORDER BY id ASC
                LIMIT $limit
                OFFSET $offset
            ");

            if (count($templates) > 0) {
                $output->writeln(sprintf('Re-compiling %d portal templates...', count($templates)));
            }

            foreach ($templates as $tpl) {
                $output->write(sprintf('  Compiling %d: %s ... ', $tpl['id'], $tpl['name']));

                try {
                    $code     = $tpl['template_code'];
                    $compiled = $twig->compileSource($code, $tpl['name']);

                    $db->update('templates', ['template_compiled' => $compiled], ['id' => $tpl['id']]);

                    unset($code);
                    unset($compiled);

                    $output->writeln('OK');
                } catch (\Exception $e) {
                    $output->writeln('ERROR: '.$e->getMessage());

                    try {
                        $db->delete('templates', ['id' => $tpl['id']]);
                    } catch (\Exception $e) {
                        $output->writeln('Failed to backup template!');

                        throw $e;
                    }
                }
            }

            $offset += $limit;
        } while (count($templates) > 0);
    }

    /**
     * @param string $type
     *
     * @return int|null
     */
    private function execSelf($type)
    {
        switch ($type) {
            case 'portal':
                $kernel = 'portal';

                break;
            case 'email':
                $kernel = 'dp';

                break;
            default:
                throw new \InvalidArgumentException();
        }

        $cmd  = $this->getContainer()->get('deskpro.app_env')->getConsolePhpCommand(sprintf('--kernel %s dp:utility:recompile-templates %s', $kernel, $type));
        $proc = new Process(
            $cmd,
            $this->getContainer()->get('deskpro.app_env')->getAppDir()
        );
        $proc->setTimeout(900);
        $proc->run(function ($type, $dat) {
            if ($type === Process::OUT) {
                fputs(STDOUT, $dat);
            } else {
                fputs(STDERR, $dat);
            }
        });

        return $proc->getExitCode();
    }

    /**
     * When we compile a lot of templates it seems to be a memory leakage somewhere inside twig service.
     * So just reload this service in container.
     *
     * @throws \ReflectionException
     */
    private function clearTwig()
    {
        $container = $this->getContainer();

        $reflection = new \ReflectionClass($container);
        $property   = $reflection->getProperty('services');
        $property->setAccessible(true);

        $services = $property->getValue($container);
        foreach ($services as $name => $service) {
            // fix twig memory leakage
            if (strpos($name, 'twig') !== false) {
                unset($services[$name]);
            }
        }

        $property->setValue($container, $services);
        unset($services);
    }
}
