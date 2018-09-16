<?php

namespace DeskPRO\Bundle\AppBundle\Command\Utility;

use Application\DeskPRO\Entity\LogEvent;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonEmail;
use Application\DeskPRO\Log\Event\EntityUpdated;
use Application\DeskPRO\ORM\StateChange\ChangeSimple;
use Application\DeskPRO\People\Purger;
use Doctrine\DBAL\Types\Type;
use Orb\Util\Numbers;
use Orb\Validator\StringEmail;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Class InvalidEmailsCommand
 * @package DeskPRO\Bundle\AppBundle\Command\Utility
 */
class InvalidEmailsCommand extends ContainerAwareCommand
{
    /**
     * Operation fix mode
     */
    const FIX_MRK = 'mark';
    const FIX_DLT = 'delete';

    /**
     * @var string
     */
    private $fix = 'mark';

    /**
     * @var bool
     */
    private $soft = false;

    /**
     * Scan limit
     * @var int
     */
    private $limit = 1000;

    /**
     * Scan offset
     * @var int
     */
    private $offset = 0;

    /**
     * @var array
     */
    private $removedPeople = [];

    /**
     * @var int
     */
    private $recordsFixed = 0;

    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $entityManager;

    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $dbConnection;

    /**
     * @var \Application\DeskPRO\Monolog\Logger
     */
    private $logger;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            // Command signature
            ->setName('dp:utility:invalid-emails')
            // Command description
            ->setDescription('Mark/Delete invalid emails')
            // Command execution mode (the only required option, as defines behaviour)
            ->addOption(
                'fix',
                'x',
                InputOption::VALUE_REQUIRED,
                'Fix options: 
                - "mark" to change the email address to "$id@email.invalid"; 
                - "delete" to permanently delete the user and all related records.',
                'mark'
            )
            // Query offfset and limit
            ->addOption(
                'offset',
                'o',
                InputOption::VALUE_OPTIONAL,
                'The record to begin at',
                $this->offset
            )
            ->addOption(
                'count',
                'c',
                InputOption::VALUE_OPTIONAL,
                'How many records to process',
                $this->limit
            )
            ->addOption(
                'soft',
                'sf',
                InputOption::VALUE_NONE,
                'Use soft mode for person deletion'
            )

            // Command --help text
            ->setHelp('Depending on the --fix option, either mark incorrect emails as invalid, or delete account')
        ;
    }

    /**
     * Initialize all needed utils
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        // Get DBAL connection
        $this->dbConnection = $this->getContainer()->get('doctrine')->getConnection();

        // Get entity manager
        $this->entityManager = $this->getContainer()->get('doctrine')->getManager();

        // Get logger
        $this->logger = $this->getContainer()->get('deskpro.logger.changelog');

        // Set soft mode
        $this->soft = $input->getOption('soft');
    }

    /**
     * Validate offset and count options
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        if (! in_array($input->getOption('fix'), [self::FIX_MRK, self::FIX_DLT])) {
            $question = new Question(
                'Please select one of the <info>' .self::FIX_MRK. '</info> or <info>'. self::FIX_DLT .'</info> fix mode: ',
                $this->fix
            );
            $question->setValidator(function ($answer) {
                if (! in_array($answer, [self::FIX_MRK, self::FIX_DLT])) {
                    throw new \RuntimeException(
                        'Fix must be either <info>'.self::FIX_MRK.'</info> or <info>'.self::FIX_DLT.'</info>'
                    );
                }

                return $answer;
            });
            $question->setMaxAttempts(2);

            $this->fix = $helper->ask($input, $output, $question);
        } else {
            $this->fix = $input->getOption('fix');
        }

        if (! Numbers::isInteger($input->getOption('offset'))) {
            $question = new Question(
                'Please enter a valid number for offset. <info>Eg.: 500</info>: ',
                $this->offset
            );
            $question->setValidator(function ($answer) {
                if (! Numbers::isInteger($answer)) {
                    throw new \RuntimeException(
                        'Offset must be a valid integer.'
                    );
                }

                return $answer;
            });
            $question->setMaxAttempts(2);

            $this->offset = $helper->ask($input, $output, $question);
        } else {
            $this->offset = (int) $input->getOption('offset');
        }

        if (! Numbers::isInteger($input->getOption('count'))) {
            $question = new Question(
                'Please enter a valid number for count. <info>Eg.: 1000</info>: ',
                $this->offset
            );
            $question->setValidator(function ($answer) {
                if (! Numbers::isInteger($answer)) {
                    throw new \RuntimeException(
                        'Count must be a valid integer.'
                    );
                }

                return $answer;
            });
            $question->setMaxAttempts(2);

            $this->limit = $helper->ask($input, $output, $question);
        } else {
            $this->limit = (int) $input->getOption('count');
        }
    }

    /**
     * Execute the command
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        // stubs
        $recordsScanned = 0;

        // Console style
        $io = new SymfonyStyle($input, $output);
        
        // display title
        $io->title(
            'Starting processing people emails' .
            ((true === $this->soft && $this->fix == self::FIX_DLT) ? ' (Soft Mode)' : '')
        );
        
        // start timer
        $timerStart = microtime(true);

        // get total records
        $total = $this->dbConnection->fetchColumn('SELECT COUNT(id) as total FROM people_emails');

        // command logic
        while (true) {
            $statement = $this->dbConnection->executeQuery('
                SELECT e.id AS email_id, e.email, e.email_domain, p.id AS person_id FROM people_emails e
                INNER JOIN people p ON e.person_id = p.id
                ORDER BY p.id
                LIMIT :limit OFFSET :offset
            ',
                [
                    'limit'  => $this->limit,
                    'offset' => $this->offset,
                ],
                [
                    'limit'  => TYPE::INTEGER,
                    'offset' => TYPE::INTEGER,
                ]
            );

            // output chunk stats
            $scanFrom = $this->offset + 1;
            $scanTill = Numbers::inRange($total, $this->offset, $this->offset + $this->limit)
                ? $total
                : $this->offset + $this->limit + 1;

            $io->writeln("Scanning <info>{$scanFrom}</info> to <info>{$scanTill}</info>");

            // increate offset
            $this->offset = $this->offset + $this->limit;

            /**
             * @todo: when the codebase is updated to the latest stack, this one
             * could simplify the logic
             */
            // $statement->setFetchMode(\PDO::FETCH_FUNC, [$this, 'proceedRecord']);

            // Because doctrine does not support \PDO::FETCH_FUNC, we have to use a workaround
            $iterator = new \ArrayIterator($statement->fetchAll());
            iterator_apply($iterator, [$this, 'proceedRecord'], [$iterator, $io]);

            // updated scanned counter
            $recordsScanned = $recordsScanned + $statement->rowCount();

            // if last recordset count is less then limit, exit
            if ($statement->rowCount() < $this->limit) {
                break;
            }
        }

        // print execution summary
        $io->newLine(2);
        $io->writeln(sprintf(
            'Scanned %d records and fixed %d records in %.2f seconds.',
            $recordsScanned, $this->recordsFixed, microtime(true) - $timerStart
        ));

        return 0;
    }

    /**
     * Proceed the record from recordset
     *
     * @param \Iterator $iterator
     * @param SymfonyStyle $io
     * @return bool
     */
    private function proceedRecord(\Iterator $iterator, SymfonyStyle $io)
    {
        // get current iterator
        $record = $iterator->current();

        // check if email is valid
        if (! StringEmail::isValueValid($record['email'])) {
            /** @var PersonEmail $personEmail */
            $personEmail = $this->entityManager->find(
                'DeskPRO:PersonEmail',
                $record['email_id']
            );

            // check if records exists
            if ($personEmail instanceof PersonEmail) {
                // apply fix according to command fix mode
                $this->{$this->fix}($personEmail, $io);
            }
        }

        // return true in order to continue iterating
        return true;
    }

    /**
     * Apply "mark: fix
     *
     * @param PersonEmail $personEmail
     * @param SymfonyStyle $io
     */
    private function mark(PersonEmail $personEmail, SymfonyStyle $io)
    {
        /** @var Person $person */
        $person = $personEmail->getPerson();

        // compile new email
        $email = "person{$person->getId()}-email{$personEmail->getId()}@email.invalid";

        // User raw query
        $this->dbConnection->executeUpdate(
            'UPDATE people_emails SET email = :email, email_domain = :tld WHERE id = :id',
            [
                'id'    => $personEmail->getId(),
                'email' => $email,
                'tld'   => 'email.invalid',
            ],
            [
                'id'    => TYPE::INTEGER,
                'email' => TYPE::STRING,
                'tld'   => TYPE::STRING,
            ]
        );

        // log event
        $logEvent = new LogEvent(
            new EntityUpdated(
                $person,
                new ChangeSimple('email', $personEmail->getEmail(), $email)
            ),
            $person
        );
        $this->logger->info($logEvent);

        // output fix information
        $io->write("Person <info>{$person->getId()}</info> ");
        $io->write("-- Email <error><{$personEmail->getEmail()}></error> invalid ");
        $io->writeln("-- Updated to <comment><{$email}></comment>");

        // increase fixed counter
        $this->recordsFixed++;
    }

    /**
     * Apply "delete" fix
     *
     * @param PersonEmail $personEmail
     * @param SymfonyStyle $io
     */
    private function delete(PersonEmail $personEmail, SymfonyStyle $io)
    {
        /** @var Person $person */
        $person = $personEmail->getPerson();

        // if not already removed
        if (! in_array($person->getId(), $this->removedPeople)) {
            // Soft or Hard delete mode
            $method = (true === $this->soft) ? 'softDeletePerson' : 'hardDeletePerson';
            $this->{$method}($personEmail, $io);
        }
    }

    /**
     * Advanced delete logic
     *
     * @param PersonEmail $personEmail
     * @param SymfonyStyle $io
     */
    private function softDeletePerson(PersonEmail $personEmail, SymfonyStyle $io)
    {
        /** @var Person $person */
        $person = $personEmail->getPerson();

        if ($person->getPrimaryEmail()->getId() === $personEmail->getId()) {
            $this->hardDeletePerson($personEmail, $io);

            return;
        }

        // if this person has more than 1 email, check other
        if ($person->emails->count() > 1) {
            // show info
            $io->write("Person <info>{$personEmail->getPerson()->getId()}</info> ");
            $io->writeln("-- Primary Email <info><{$person->getPrimaryEmail()->getEmail()}></info> valid ");

            $invalidEmails = $person->emails->filter(function (PersonEmail $email) {
                return ! StringEmail::isValueValid($email->getEmail());
            });

            if (! $invalidEmails->isEmpty()) {
                /** @var PersonEmail $invalidEmail */
                foreach ($invalidEmails as $invalidEmail) {
                    if ($person->getPrimaryEmail()->getId() === $invalidEmail->getId()) {
                        $this->hardDeletePerson($personEmail, $io);

                        return;
                    }

                    // delete email
                    $this->dbConnection->delete('people_emails', ['id' => $invalidEmail->getId()]);

                    // output fix information
                    $io->write("Person <info>{$personEmail->getPerson()->getId()}</info> ");
                    $io->write("-- Secondary Email <error><{$invalidEmail->getEmail()}></error> invalid ");
                    $io->writeln("-- <comment>Email removed</comment>");

                    $this->recordsFixed++;
                }

                return;
            }
        }
    }

    /**
     * Simple delete person logic
     *
     * @param PersonEmail $personEmail
     * @param SymfonyStyle $io
     */
    private function hardDeletePerson(PersonEmail $personEmail, SymfonyStyle $io)
    {
        // delete person
        (new Purger($personEmail->getPerson(), $this->entityManager))->purge();

        // add person to deleted cache
        $this->removedPeople[] = $personEmail->getPerson()->getId();

        // output fix information
        // show info
        $io->write("Person <info>{$personEmail->getPerson()->getId()}</info> ");
        $io->write("-- Email <error><{$personEmail->getEmail()}></error> invalid ");
        $io->writeln("-- <error>Person deleted</error>");

        $this->recordsFixed++;
    }
}
