<?php

namespace DeskPRO\Bundle\AppBundle\Command\Utility;

use Application\DeskPRO\People\PasswordPolicyValidator;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class RequirePasswordResetCommand
 */
class RequirePasswordResetCommand extends ContainerAwareCommand
{
    const ARG_ADMINS = 'admins';
    const ARG_AGENTS = 'agents';
    const ARG_USERS  = 'users';
    const ARG_ALL    = 'all';

    /**
     * @var \Doctrine\DBAL\Connection
     */
    protected $dbConnection;

    /**
     * @var array
     */
    protected $validArgumentValues = [
        self::ARG_ADMINS,
        self::ARG_AGENTS,
        self::ARG_USERS,
        self::ARG_ALL,
    ];

    /**
     * @var array
     */
    protected $criteriaMap = [
        self::ARG_ADMINS => 'can_admin = 1',
        self::ARG_AGENTS => '(is_agent = 1 AND can_admin = 0)',
        self::ARG_USERS  => '(is_user = 1 AND is_agent = 0 AND can_admin = 0)',
    ];

    /**
     * {@inheritDoc}
     */
    protected function configure()
    {
        $this
            ->setName('dp:require-password-reset')
            ->setDescription('Mark passwords as required to reset.')
            ->addArgument(
                'role',
                InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                'The user roles which passwords will be marked as required to reset. Defaults to all.
                    Possible values: "admins", "agents", "users", "all"
                ',
                ['all']
            );
    }

    /**
     * {@inheritDoc}
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->dbConnection = $this->getContainer()->get('doctrine')->getConnection();
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \Doctrine\DBAL\ConnectionException
     *
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $roles = $input->getArgument('role');

        $criterias = [];

        if (in_array(self::ARG_ALL, $roles, true)) {
            $criterias = $this->criteriaMap;
        } else {
            foreach ($roles as $role) {
                if (!in_array($role, $this->validArgumentValues, true)) {
                    $output->writeln("<warning>Invalid role: {$role}. Skip.</warning>");

                    continue;
                }

                $criterias[] = $this->criteriaMap[$role];
            }

            if (empty($criterias)) {
                $output->writeln('<error>Unknown roles argument.</error>');

                return 1;
            }
        }

        $criteriaQuery = implode(' OR ', $criterias);

        $this->dbConnection->beginTransaction();

        try {
            $this->dbConnection->executeUpdate("
                    UPDATE people
                    SET date_password_set = :magic_date
                    WHERE
                        password IS NOT NULL
                        AND ({$criteriaQuery})
                ",
                [
                    'magic_date' => PasswordPolicyValidator::MAGIC_PASSWORD_RESET_REQUIRED,
                ]
            );

            $this->dbConnection->commit();
        } catch (\Exception $exception) {
            $this->dbConnection->rollBack();

            $output->writeln("<error>{$exception->getMessage()}</error>");
            $output->write($exception->getTrace(), true);

            return 1;
        }
    }
}
