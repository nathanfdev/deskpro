<?php

/*
 * DeskPRO (r) has been developed by DeskPRO Ltd. https://www.deskpro.com/
 * a British company located in London, England.
 *
 * All source code and content Copyright (c) 2017, DeskPRO Ltd.
 *
 * The license agreement under which this software is released
 * can be found at https://www.deskpro.com/eula/
 *
 * By using this software, you acknowledge having read the license
 * and agree to be bound thereby.
 *
 * Please note that DeskPRO is not free software. We release the full
 * source code for our software because we trust our users to pay us for
 * the huge investment in time and energy that has gone into both creating
 * this software and supporting our customers. By providing the source code
 * we preserve our customers' ability to modify, audit and learn from our
 * work. We have been developing DeskPRO since 2001, please help us make it
 * another decade.
 *
 * Like the work you see? Think you could make it better? We are always
 * looking for great developers to join us: http://www.deskpro.com/jobs/
 *
 * ~ Thanks, Everyone at Team DeskPRO
 */

namespace DeskPRO\Bundle\AppBundle\Command\Utility;

use Application\EmailBundle\Twig\PreProcessor\EmailPreProcessor as LegacyEmailPreProcessor;
use DeskPRO\Bundle\InstallBundle\Backup\RecordBackuper;
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
        $db   = $this->getContainer()->get('database_connection');
        $twig = $this->getContainer()->get('templating.new_email.twig');

        $templates = $db->fetchAll("
            SELECT *
            FROM templates
            WHERE name LIKE 'SendmailBundle:%'
            ORDER BY id ASC
        ");

        $output->writeln(sprintf('Re-compiling %d email templates...', count($templates)));

        foreach ($templates as $tpl) {
            $output->write(sprintf('  Compiling %d: %s ... ', $tpl['id'], $tpl['name']));
            $this->backupTpl($tpl);

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
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \Exception
     */
    private function recompileLegacyEmailTemplates(InputInterface $input, OutputInterface $output)
    {
        $db   = $this->getContainer()->get('database_connection');
        $twig = $this->getContainer()->get('templating.email.twig');

        $templates = $db->fetchAll("
            SELECT *
            FROM templates
            WHERE name LIKE 'DeskPRO:emails%' OR name LIKE 'DeskPRO:custom_emails%'
            ORDER BY id ASC
        ");

        $output->writeln(sprintf('Re-compiling %d legacy email templates...', count($templates)));

        foreach ($templates as $tpl) {
            $output->write(sprintf('  Compiling %d: %s ... ', $tpl['id'], $tpl['name']));
            $this->backupTpl($tpl);

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
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \Exception
     */
    private function recompilePortalTemplates(InputInterface $input, OutputInterface $output)
    {
        $db   = $this->getContainer()->get('database_connection');
        $twig = $this->getContainer()->get('twig');

        $templates = $db->fetchAll("
            SELECT *
            FROM templates
            WHERE name LIKE 'Theme:%'
            ORDER BY id ASC
        ");

        $output->writeln(sprintf('Re-compiling %d portal templates...', count($templates)));

        foreach ($templates as $tpl) {
            $output->write(sprintf('  Compiling %d: %s ... ', $tpl['id'], $tpl['name']));
            $this->backupTpl($tpl);

            try {
                $code     = $tpl['template_code'];
                $compiled = $twig->compileSource($code, $tpl['name']);

                $db->update('templates', ['template_compiled' => $compiled], ['id' => $tpl['id']]);

                $output->writeln('OK');
            } catch (\Exception $e) {
                $output->writeln('ERROR: '.$e->getMessage());
                try {
                    $this->backupTpl($tpl);
                    $db->delete('templates', ['id' => $tpl['id']]);
                } catch (\Exception $e) {
                    $output->writeln('Failed to backup template!');
                    throw $e;
                }
            }
        }
    }

    /**
     * @param array $tpl
     */
    private function backupTpl(array $tpl)
    {
        $b    = new RecordBackuper($this->getContainer()->get('deskpro.app_env')->getUserBackupsDir());
        $meta = $tpl;
        unset($meta['template_code'], $meta['template_compiled']);
        $b->backupRecord('templates', $tpl['id'].'-'.$tpl['name'], $tpl['template_code'], $meta);
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
}
