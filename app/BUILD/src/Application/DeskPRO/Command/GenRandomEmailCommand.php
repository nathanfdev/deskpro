<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Command;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use DeskPRO\Component\Util\RandUtils;
use Orb\Util\Strings;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenRandomEmailCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('dp:gen-rand-email');
        $this->addOption('from', null, InputOption::VALUE_REQUIRED, 'An email address to send from. Create a user first if you want to send a name as well. You can use %RAND% as a placeholder for a random value.');
        $this->addOption('to', null, InputOption::VALUE_REQUIRED, 'An email address or a ticket account ID. If none supplied, the first ticket account in the DB is chosen. Note: Does not NEED to be a ticket account, but generaly is.');
        $this->addOption('cc', null, InputOption::VALUE_REQUIRED, 'An email address or a comma-separated list.');
        $this->addOption('tpl', null, InputOption::VALUE_REQUIRED, 'The template to use: text, html, fwd, fwd_with_reply');
        $this->addOption('subject', null, InputOption::VALUE_REQUIRED, "A subject line. Defaults to a generated one. Prefix with 'twig:' to pass the subject string through twig.");
        $this->addOption('message', null, InputOption::VALUE_REQUIRED, "A message. Defaults to a generated one. Prefix with 'twig:' to pass the string through twig.");
        $this->addOption('message-length', null, InputOption::VALUE_REQUIRED, 'Message length, using faker to gen random text.');
        $this->addOption('message-file', null, InputOption::VALUE_REQUIRED, "A file containing a message. Prefix with 'twig:' to pass the file through twig.");
        $this->addOption('ticket-reply', null, InputOption::VALUE_REQUIRED, 'Make this a reply to this ticket ID. If the --from is an agent, then it will be as an agent reply.');
        $this->addOption('vars', null, InputOption::VALUE_REQUIRED, 'Extra vars to make available to the templates. Should be a JSON encoded string');
        $this->addOption('is-bounce', null, InputOption::VALUE_NONE, 'Set is_bounce=true in vars');
    }

    /**
     * @return \Application\DeskPRO\DependencyInjection\DeskproContainer
     */
    public function getContainer()
    {
        return parent::getContainer();
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        //------------------------------
        // From
        //------------------------------

        $fromOpt   = $input->getOption('from');
        $fromEmail = null;
        $fromUser  = null;
        $fromName  = null;
        $fromLine  = null;
        if ($fromOpt) {
            $fromEmail = $fromOpt;
            $fromUser  = $this->getContainer()->getEm()->getRepository(Person::class)->findOneByEmail($fromEmail);
            if ($fromUser) {
                $fromName = $fromUser->getDisplayName();
            }
        }

        if (!$fromEmail) {
            $output->writeln('<error>You must supply --from</error>');

            return 1;
        }

        if ($fromName) {
            $fromLine = $fromName.' <'.$fromEmail.'>';
        } else {
            $fromLine = $fromEmail;
        }

        $fromEmail = str_replace('%RAND%', RandUtils::randomStringFormat('%10A'), $fromEmail);

        //------------------------------
        // To
        //------------------------------

        $toOpt     = $input->getOption('to');
        $toAccount = null;
        $toEmail   = null;
        if ($toOpt) {
            if (ctype_digit($toOpt)) {
                try {
                    $toAcc = $this->getContainer()->getEmailAccountManager()->getAccount($toOpt);
                } catch (\Exception $e) {
                    $output->writeln("<error>No such ticket account: $toOpt</error>");

                    return 1;
                }
            } else {
                $toAcc = $this->getContainer()->getEmailAccountManager()->findAccountForEmailAddress($toOpt);
            }
        } else {
            try {
                $toAcc = $this->getContainer()->getEmailAccountManager()->getPrimaryTicketAccount();
            } catch (\Exception $e) {
                $output->writeln('<error>No --to option supplied and this database has no ticket account to use as a default. Try again with --to.</error>');

                return 1;
            }
        }

        $ccOpt   = $input->getOption('cc');
        $ccEmail = null;
        if ($ccOpt) {
            $ccEmail = $ccOpt;
        }

        if ($toAcc) {
            $toEmail = $toAcc->getUseEmailAddress();
        } else {
            $toEmail = $toOpt;
        }

        //------------------------------
        // As reply
        //------------------------------

        $ticket     = null;
        $accessCode = null;

        $replyOpt = $input->getOption('ticket-reply');

        if ($replyOpt) {
            $ticket = $this->getContainer()->getEm()->find(Ticket::class, $replyOpt);
            if (!$ticket) {
                $output->writeln("<error>--ticket-reply: No such ticket: $replyOpt</error>");

                return 1;
            }

            if ($fromUser && $fromUser->is_agent) {
                $tac = $ticket->findAccessCodeForPerson($fromUser);
                if (!$tac) {
                    $tac = $ticket->addAccessCodeForPerson($fromUser);
                    $this->getContainer()->getEm()->persist($tac);
                    $this->getContainer()->getEm()->flush();
                }
                $accessCode = $tac->getAccessCode();
            } else {
                $accessCode = $ticket->getAccessCode();
            }
        }

        //------------------------------
        // Subject and message
        //------------------------------

        $faker = \Faker\Factory::create();

        $subject = $input->getOption('subject');
        if (!$subject) {
            if ($ticket) {
                $subject = 'RE: '.$ticket->subject;
            } else {
                $subject = sprintf('Test Message #%s -- %s -- %s', date('Hi'), date('Y-m-d'), date('s'));
            }
        }

        if ($messageFile = $input->getOption('message-file')) {
            $isTwig = false;
            if (substr($messageFile, 0, 5) === 'twig:') {
                $isTwig      = true;
                $messageFile = substr($messageFile, 5);
            }

            $message = file_get_contents($messageFile);
            if (!$message) {
                $output->writeln('<error>--message-file: No such file (or the file is empty)</error>');

                return 1;
            }

            if ($isTwig) {
                $message = 'twig:'.$message;
            }
        } else {
            if ($input->getOption('message')) {
                $message = $input->getOption('message');
            } elseif ($input->getOption('message-length')) {
                $message = $faker->realText($input->getOption('message-length'));
            } else {
                $message = sprintf('Test Message #%s -- %s -- %s', date('Hi'), date('Y-m-d'), date('s'));
            }
        }

        //------------------------------
        // Tpl
        //------------------------------

        $tpl = 'DeskPRO:dev:gen_email/'.($input->getOption('tpl') ?: 'html').'.txt.twig';

        $vars = [
            'uid'      => uniqid('dp', true),
            'ts'       => time(),
            'rand'     => mt_rand(100000, 999999),
            'rand_ref' => Strings::random(4, Strings::CHARS_ALPHA_IU).'-'.Strings::random(4, Strings::CHARS_ALPHA_IU).'-'.Strings::random(4, Strings::CHARS_ALPHA_IU),
            'rand_str' => Strings::random(10, Strings::CHARS_ALPHA_IU),
            'subject'  => $subject,
            'message'  => $message,

            'as_reply'    => $ticket,
            'as_agent'    => $fromUser && $fromUser->isAgent(),
            'access_code' => $accessCode,

            'from_user'  => $fromUser,
            'from_email' => $fromEmail,
            'from_name'  => $fromName,
            'from_line'  => $fromLine,

            'to_email' => $toEmail,
            'to_acc'   => $toAcc,

            'cc_email' => $ccEmail,
        ];

        $customVars = null;
        if ($customVars = $input->getOption('vars')) {
            $customVars = @json_decode($customVars, true);
        }
        if (!$customVars) {
            $customVars = [];
        }

        if ($customVars) {
            $vars = array_merge($vars, $customVars);
        }

        if ($input->getOption('is-bounce')) {
            $vars['is_bounce'] = true;
        }

        $procKeys   = array_keys($customVars);
        $procKeys[] = 'subject';
        $procKeys[] = 'message';

        $twig = $this->getContainer()->getTwig();
        foreach ($procKeys as $k) {
            if (substr($vars[$k], 0, 5) === 'twig:') {
                $vars[$k] = $twig->renderStringTemplate(substr($vars[$k], 5), $vars);
            }
        }

        //------------------------------
        // Done
        //------------------------------

        echo trim($this->getContainer()->get('templating.email')->render($tpl, $vars));
        echo "\n";

        return 0;
    }
}
