<?php

/**
 * DeskPRO.
 */

namespace Application\DeskPRO\Command;

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
        $this->addOption('from', null, InputOption::VALUE_REQUIRED, 'An email address to send from. Create a user first if you want to send a name as well. You can use %RAND% as a palceholder for a random value.');
        $this->addOption('to', null, InputOption::VALUE_REQUIRED, 'An email address or a ticket account ID. If none supplied, the first ticket account in the DB is chosen. Note: Does not NEED to be a ticket account, but generaly is.');
        $this->addOption('tpl', null, InputOption::VALUE_REQUIRED, 'The template to use: text, html, fwd, fwd_with_reply');
        $this->addOption('subject', null, InputOption::VALUE_REQUIRED, "A subject line. Defults to a generated one. Prefix with 'twig:' to pass the subject string throug twig.");
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

        $from_opt   = $input->getOption('from');
        $from_email = null;
        $from_user  = null;
        $from_name  = null;
        $from_line  = null;
        if ($from_opt) {
            $from_email = $from_opt;
            $from_user  = $this->getContainer()->getEm()->getRepository('DeskPRO:Person')->findOneByEmail($from_email);
            if ($from_user) {
                $from_name = $from_user->getDisplayName();
            }
        }

        if (!$from_email) {
            $output->writeln('<error>You must supply --from</error>');

            return 1;
        }

        if ($from_name) {
            $from_line = $from_name.' <'.$from_email.'>';
        } else {
            $from_line = $from_email;
        }

        $from_email = str_replace('%RAND%', RandUtils::randomStringFormat('%10A'), $from_email);

        //------------------------------
        // To
        //------------------------------

        $to_opt     = $input->getOption('to');
        $to_account = null;
        $to_email   = null;
        if ($to_opt) {
            if (ctype_digit($to_opt)) {
                try {
                    $to_acc = $this->getContainer()->getEmailAccountManager()->getAccount($to_opt);
                } catch (\Exception $e) {
                    $output->writeln("<error>No such ticket account: $to_opt</error>");

                    return 1;
                }
            } else {
                $to_acc = $this->getContainer()->getEmailAccountManager()->findAccountForEmailAddress($to_opt);
            }
        } else {
            try {
                $to_acc = $this->getContainer()->getEmailAccountManager()->getPrimaryTicketAccount();
            } catch (\Exception $e) {
                $output->writeln('<error>No --to option supplied and this database has no ticket account to use as a default. Try again with --to.</error>');

                return 1;
            }
        }

        if ($to_acc) {
            $to_email = $to_acc->getUseEmailAddress();
        } else {
            $to_email = $to_opt;
        }

        //------------------------------
        // As reply
        //------------------------------

        $ticket      = null;
        $access_code = null;

        $reply_opt = $input->getOption('ticket-reply');

        if ($reply_opt) {
            $ticket = $this->getContainer()->getEm()->find('DeskPRO:Ticket', $reply_opt);
            if (!$ticket) {
                $output->writeln("<error>--ticket-reply: No such ticket: $reply_opt</error>");

                return 1;
            }

            if ($from_user && $from_user->is_agent) {
                $tac = $ticket->findAccessCodeForPerson($from_user);
                if (!$tac) {
                    $tac = $ticket->addAccessCodeForPerson($from_user);
                    $this->getContainer()->getEm()->persist($tac);
                    $this->getContainer()->getEm()->flush();
                }
                $access_code = $tac->getAccessCode();
            } else {
                $access_code = $ticket->getAccessCode();
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

        if ($message_file = $input->getOption('message-file')) {
            $is_twig = false;
            if (substr($message_file, 0, 5) === 'twig:') {
                $is_twig      = true;
                $message_file = substr($message_file, 5);
            }

            $message = file_get_contents($message_file);
            if (!$message) {
                $output->writeln('<error>--message-file: No such file (or the file is empty)</error>');

                return 1;
            }

            if ($is_twig) {
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
            'as_agent'    => $from_user && $from_user->isAgent(),
            'access_code' => $access_code,

            'from_user'  => $from_user,
            'from_email' => $from_email,
            'from_name'  => $from_name,
            'from_line'  => $from_line,

            'to_email' => $to_email,
            'to_acc'   => $to_acc,
        ];

        $custom_vars = null;
        if ($custom_vars = $input->getOption('vars')) {
            $custom_vars = @json_decode($custom_vars, true);
        }
        if (!$custom_vars) {
            $custom_vars = [];
        }

        if ($custom_vars) {
            $vars = array_merge($vars, $custom_vars);
        }

        if ($input->getOption('is-bounce')) {
            $vars['is_bounce'] = true;
        }

        $proc_keys   = array_keys($custom_vars);
        $proc_keys[] = 'subject';
        $proc_keys[] = 'message';

        $twig = $this->getContainer()->getTwig();
        foreach ($proc_keys as $k) {
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
