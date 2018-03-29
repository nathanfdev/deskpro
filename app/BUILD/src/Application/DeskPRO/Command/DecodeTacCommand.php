<?php

/**
 * DeskPRO.
 *
 * @category Commands
 */

namespace Application\DeskPRO\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DecodeTacCommand extends \Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand
{
    protected function configure()
    {
        $this->setDefinition([
            new InputArgument('tac', InputArgument::REQUIRED, 'The TAC or PTAC to decode. These are the codes that usually begin with TAC- or PTAC- or TICKET- in email headers.'),
        ])->setName('dp:decode-tac');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $tac = $input->getArgument('tac');
        $tac = preg_replace('#^(TAC|PTAC|TICKET)\-#', '', $tac);

        $info = \Application\DeskPRO\Entity\Ticket::decodeAccessCode($tac);
        if ($info) {
            echo "ID: {$info['ticket_id']}\nAuth: {$info['auth']}\n";
        } else {
            echo "Invalid code\n";
        }
    }
}
