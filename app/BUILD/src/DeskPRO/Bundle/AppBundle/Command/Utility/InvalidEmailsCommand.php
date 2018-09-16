<?php

namespace DeskPRO\Bundle\AppBundle\Command\Utility;

use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\PersonEmail;
use Application\DeskPRO\People\Purger;
use Application\DeskPRO\EntityRepository\Person as PersonRepository;
use Application\DeskPRO\EntityRepository\PersonEmail as PersonEmailRepository;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\EntityManager;
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
     * @var int
     */
    private $recordsFixed = 0;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var Connection
     */
    private $dbConnection;

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
        // $this->dbConnection = $this->getContainer()->get('doctrine.dbal.default_connection');
        $this->dbConnection = $this->getContainer()->get('doctrine')->getConnection();

        // Get entity manager
        //$this->entityManager = $this->getContainer()->get('doctrine.orm.default_entity_manager');
        $this->entityManager = $this->getContainer()->get('doctrine')->getManager();
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
        $io->title('Starting processing people emails');
        
        // start timer
        $timerStart = microtime(true);

        // get total records
        $total = $this->dbConnection->fetchColumn('SELECT COUNT(id) as total FROM people_emails');

        // command logic
        while (true) {
            $statement = $this->dbConnection->executeQuery('
                SELECT e.id AS email_id, e.email, p.id AS person_id FROM people_emails e
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
            'Scanned %d records and %sed %d records in %.2f seconds.',
            $recordsScanned, $this->fix, $this->recordsFixed, microtime(true) - $timerStart
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

        // $output->writeln("Processing email ... {$record['email']}");

        // check if email is valid
        if (! StringEmail::isValueValid($record['email'])) {
            // output info
            $io->write("Person <info>{$record['person_id']}</info> ");
            $io->write("-- Email <error><{$record['email']}></error> invalid ");

            // apply fix according to command fix mode
            $this->{$this->fix}($record, $io);
        }

        // return true in order to continue iterating
        return true;
    }

    /**
     * Apply "mark: fix
     *
     * @param array $record
     * @param SymfonyStyle $io
     */
    private function mark(array $record, SymfonyStyle $io)
    {
        // compile new email
        $email = "person{$record['person_id']}-email{$record['email_id']}@email.invalid";

        // get entity repository so we could retrieve email entity
        /** @var PersonEmailRepository $entityRepository */
        $entityRepository = $this->entityManager->getRepository('DeskPRO:PersonEmail');

        // lookup person by it
        /** @var PersonEmail $personEmail */
        $personEmail = $entityRepository->find($record['email_id']);

        // update email and TLD
        $personEmail->setEmail($email);
        $this->entityManager->persist($personEmail);
        $this->entityManager->flush();

        // output fix information
        $io->writeln("-- Updated to <comment><{$email}></comment>");

        // increase fixed counter
        $this->recordsFixed++;
    }

    /**
     * Apply "delete" fix
     *
     * @param array $record
     * @param SymfonyStyle $io
     */
    private function delete(array $record, SymfonyStyle $io)
    {
        // get entity repository so we could retrieve person entity
        /** @var PersonRepository $entityRepository */
        $entityRepository = $this->entityManager->getRepository('DeskPRO:Person');

        // lookup person by it
        /** @var Person $person */
        $person = $entityRepository->find($record['person_id']);

        // init the purger and delete the user
        (new Purger($person, $this->entityManager))->purge();

        // output fix information// Person 1003 -- Email <foobar> invalid -- Updated to <person1003-email1055@email.invalid>
        $io->writeln("-- <error>Person deleted</error>");

        $this->recordsFixed++;
    }
}
