<?php

/**
 * DeskPRO.
 */

namespace DpTestSrc\TestBundle\DataSet;

use Application\DeskPRO\Entity\AgentTeam;
use Application\DeskPRO\Entity\Brand;
use Application\DeskPRO\Entity\CustomDataTicket;
use Application\DeskPRO\Entity\CustomDefTicket;
use Application\DeskPRO\Entity\Department;
use Application\DeskPRO\Entity\Person;
use Application\DeskPRO\Entity\Ticket;
use Application\DeskPRO\Entity\TicketMessage;
use Application\InstallBundle\Data\DefaultDataProcessor;
use DeskPRO\Bundle\AppBundle\Entity\TicketFilter;
use DeskPRO\Bundle\AppBundle\Entity\TicketFilterSet;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\Agent\AgentTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\CompositeTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\Term\TicketCustomData\TicketCustomDataTerm;
use DeskPRO\Bundle\AppBundle\TermEngine\TermInterface;
use Doctrine\ORM\EntityManager;
use DpTestSrc\TestBundle\UserDetailsRepo;

/**
 * Class TermEngineDb.
 */
class TermEngineDb extends AbstractDbSet
{
    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return 'term engine';
    }

    /**
     * {@inheritdoc}
     */
    protected function installSet()
    {
        $em = $this->getEm();

        //------------------------------
        // Init data
        //------------------------------

        // users
        list($admin, $agent, $agent_chris, $user) = $this->addBasicUsers();

        // install
        $this->addBaseBrand($em);
        $this->runBasicDataInstall($admin, $em);

        /** @var Department $support */
        $support = $this->getEm()->find(Department::class, 1);
        /** @var Department $sales */
        $sales = $this->getEm()->find(Department::class, 2);
        /** @var Brand $brand */
        $brand      = $this->getEm()->find(Brand::class, 1);
        $team_both  = $this->createAgentTeam('Agent And Chris', [$agent, $agent_chris]);
        $team_agent = $this->createAgentTeam('Agent And Chris', [$agent]);

        // the ticket numbers in the titles are purposely hardcoded
        // because they represent the insert IDs used in tests
        $this->createTicket('Test Ticket 1', $support, $brand, $user, $agent);
        $this->createTicket('Test Ticket 2', $support, $brand, $user, $agent);
        $this->createTicket('Test Ticket 3', $support, $brand, $user, $admin);
        $this->createTicket('Test Ticket 4', $support, $brand, $user, $agent);
        $this->createTicket('Test Ticket 5', $support, $brand, $user, $admin);
        $this->createTicket('Test Ticket 6', $sales, $brand, $user, $agent);
        $this->createTicket('Test Ticket 7', $sales, $brand, $user, $agent);
        $this->createTicket('Test Ticket 8', $support, $brand, $user, $agent);

        $agent_participates = $this->createTicket('Test Ticket 9', $support, $brand, $user, $admin);
        $agent_participates->addParticipantPerson($agent);

        $this->createTicket('Test Ticket 10', $support, $brand, $user, $agent, $team_both);

        $agent_participates = $this->createTicket('Test Ticket 11', $support, $brand, $user, $agent_chris, $team_both);
        $agent_participates->addParticipantPerson($agent);
        $agent_participates = $this->createTicket('Test Ticket 12', $support, $brand, $user, $agent_chris, $team_both);
        $agent_participates->addParticipantPerson($agent);
        $agent_participates = $this->createTicket('Test Ticket 13', $support, $brand, $user, $agent_chris, $team_agent);
        $agent_participates->addParticipantPerson($agent);

        $this->createTicket('Test Ticket 14', $support, $brand, $user, $agent_chris, $team_agent);
        $this->createTicket('Test Ticket 15', $support, $brand, $user, $agent_chris);
        $this->createTicket('Unassigned But Has Team - Ticket 16', $support, $brand, $user, null, $team_agent);
        $this->createTicket('UNASSIGNED Test Ticket 17', $support, $brand, $user);
        $this->createTicket('UNASSIGNED Test Ticket 18', $support, $brand, $user);
        $this->createTicket('UNASSIGNED Test Ticket 19', $support, $brand, $user);
        $this->createTicket('UNASSIGNED Test Ticket 20', $support, $brand, $user);
        $this->createTicket(
            'UNASSIGNED Test Ticket 21',
            $support,
            $brand,
            $user,
            $agent_chris,
            null,
            Ticket::STATUS_AWAITING_USER
        );
        $this->createTicket('UNASSIGNED Test Ticket 22', $support, $brand, $user, $admin, null,
        Ticket::STATUS_AWAITING_USER);
        $this->createTicket('UNASSIGNED Test Ticket 23', $support, $brand, $user, $admin, null,
            Ticket::STATUS_RESOLVED);
        $this->createTicket('UNASSIGNED Test Ticket 24', $support, $brand, $user, $admin, null,
            Ticket::STATUS_RESOLVED);
        $this->createTicket('UNASSIGNED Test Ticket 25', $support, $brand, $user, $admin, null,
            Ticket::STATUS_ARCHIVED);
        $this->createTicket(
            'UNASSIGNED Test Ticket 26',
            $support,
            $brand,
            $user,
            $admin,
            null,
            Ticket::STATUS_HIDDEN.'.'.Ticket::HIDDEN_STATUS_SPAM
        );
        $this->createTicket(
            'UNASSIGNED Test Ticket 27',
            $support,
            $brand,
            $user,
            $admin,
            null,
            Ticket::STATUS_HIDDEN.'.'.Ticket::HIDDEN_STATUS_SPAM
        );
        $this->createTicket(
            'UNASSIGNED Test Ticket 28',
            $support,
            $brand,
            $user,
            $admin,
            null,
            Ticket::STATUS_HIDDEN.'.'.Ticket::HIDDEN_STATUS_DELETED
        );

        $this->createCustomDataAndCustomFilters();

        $this->getEm()->flush();
    }

    protected function createTicket(
        $subject,
        Department $dep,
        Brand $brand,
        Person $person,
        Person $agent = null,
        AgentTeam $agent_team = null,
        $status = Ticket::STATUS_AWAITING_AGENT,
        $message = 'Test Message'
    ) {
        $ticket = new Ticket();
        $ticket->disableAutoTicketProcess();
        $ticket->creation_system = Ticket::CREATED_WEB_PERSON;
        $ticket->setAgent($agent);
        $ticket->person = $person;
        $ticket->setDepartment($dep);
        $ticket->subject = 'Test';
        $ticket->setStatus($status);
        $ticket->agent_team = $agent_team;
        $ticket->setBrand($brand);

        $message          = new TicketMessage();
        $message->person  = $person;
        $message->ticket  = $ticket;
        $message->message = 'Test Ticket';
        $ticket->addMessage($message);

        $ticket->recomputeHash();

        $this->getEm()->persist($ticket);
        $this->getEm()->persist($message);
        $this->getEm()->flush();

        return $ticket;
    }

    /**
     * @return \Application\DeskPRO\Entity\Person
     */
    protected function addBasicUsers()
    {
        $admin = $this->addUser(
            UserDetailsRepo::ADMIN_FIRST_NAME,
            UserDetailsRepo::ADMIN_LAST_NAME,
            UserDetailsRepo::ADMIN_EMAIL,
            UserDetailsRepo::ADMIN_PASS,
            true,
            true
        );

        $agent = $this->addUser(
            UserDetailsRepo::AGENT_FIRST_NAME,
            UserDetailsRepo::AGENT_LAST_NAME,
            UserDetailsRepo::AGENT_EMAIL,
            UserDetailsRepo::AGENT_PASS,
            true,
            false
        );

        $agent_chris = $this->addUser(
            UserDetailsRepo::AGENT_CHRIS_FIRST_NAME,
            UserDetailsRepo::AGENT_CHRIS_LAST_NAME,
            UserDetailsRepo::AGENT_CHRIS_EMAIL,
            UserDetailsRepo::AGENT_CHRIS_PASS,
            true,
            false
        );

        $user = $this->addUser(
            UserDetailsRepo::USER_FIRST_NAME,
            UserDetailsRepo::USER_LAST_NAME,
            UserDetailsRepo::USER_EMAIL,
            UserDetailsRepo::USER_PASS,
            false,
            false
        );

        $this->getDb()->insert('permissions', ['person_id' => $admin->id, 'name' => 'admin.use', 'value' => 1]);

        return [$admin, $agent, $agent_chris, $user];
    }

    /**
     * @param $em
     */
    protected function addBaseBrand($em)
    {
        // data.php actually does this now, so we don't need it atm
        // we need a brand
        //$brand = new Brand();
        //$em->persist($brand);
        //$em->flush();
    }

    /**
     * @param Person        $admin
     * @param EntityManager $em
     *
     * @throws \Exception
     */
    protected function runBasicDataInstall($admin, $em)
    {
        // Install data stuff
        $AGENTGROUP_ALL     = null; // should be defined by the time we finish processing data.php
        $USERGROUP_EVERYONE = null; // should be defined by the time we finish processing data.php
        $AGENT              = $admin; // can be used in data.php
        $WEB_INSTALL        = true;
        $IMPORT_INSTALL     = false;

        $install_data = new \Application\InstallBundle\Install\InstallDataReader(
            DP_ROOT.'/src/Application/InstallBundle/Data/data.php'
        );
        $translate = $this->getContainer()->get('deskpro.core.translate');

        foreach ($install_data as $php) {
            eval($php);
        }

        $em->flush();

        $data_proc = new DefaultDataProcessor($this->getContainer());
        $data_proc->runInstall();

        // For the all agent group, fetch permissions from the template
        if ($AGENTGROUP_ALL) {
            $ch = new \Application\DeskPRO\ORM\CollectionHelper($admin, 'usergroups');
            $ch->setCollection([$AGENTGROUP_ALL]);
            $em->persist($admin);
            $em->flush();
        }

        if ($USERGROUP_EVERYONE) {
            $scanner = new \Application\InstallBundle\Data\UserGroupPermScanner();
            foreach ($scanner->getNames() as $p_name) {
                $p            = new \Application\DeskPRO\Entity\Permission();
                $p->usergroup = $USERGROUP_EVERYONE;
                $p->name      = $p_name;
                $p->value     = 1;
                $em->persist($p);
            }
            $em->flush();
        }

        $data_init             = new \Application\InstallBundle\Data\DataInitializer($this->getContainer());
        $data_init->admin_user = $admin;
        $data_init->run();
// initial settings so we are "installed"

        $this->getDb()->exec(
            "
            REPLACE INTO `settings` (`name`, `value`)
            VALUES
                ('portal.default_brand', '1'),
                ('core.app_secret', 'YXI5Z2HSQ9IF8KROQQ63GL4FB4CV57ZIIZ7CZO68FUDYBZIP2M'),
                ('core.cron_logreport.cli-phperr.log', '1380716762'),
                ('core.default_from_email', 'noreply@example.com'),
                ('core.default_timezone', 'UTC'),
                ('core.deskpro_build', '".time()."'),
                ('core.deskpro_url', 'http://localhost:8888/'),
                ('core.deskpro_version', '20131002122551'),
                ('core.done_data_initializer', '1'),
                ('core.install_build', '".time()."'),
                ('core.install_key', '6S7X77ZAR2CYSDT4GJCJ'),
                ('core.install_timestamp', '".time()."'),
                ('core.install_token', 'PUGYIA9E82Z8JCPKO0NKGC957HITHNZRFHY4CQ3V1380214398'),
                ('core.last_cron_run', '".time()."'),
                ('core.last_cron_start', '".time()."'),
                ('core.license', '".$this->getLicenseKey()."'),
                ('core.rewrite_urls', '1'),
                ('core.setup_initial', '1'),
                ('core.task_completed_add_ticketfield', '".time()."'),
                ('core.twitter_last_cleanup', '".time()."'),
                ('core.use_agent_team', '1'),
                ('core_tickets.enable_like_search_auto', '1'),
                ('user.kb_subscriptions_last', '".time()."');
        "
        );

        // disable http cache
        $this->getDb()->exec(
            "
            REPLACE INTO `settings` (`name`, `value`)
            VALUES
                ('portal.http_cache_last_modified', '0'),
                ('portal.http_cache_etags', '0'),
                ('portal.smaxage_guest_tag', '0'),
                ('portal.smaxage_guest_page', '0'),
                ('portal.smaxage_user_page', '0'),
                ('portal.smaxage_user_tag', '0');
        "
        );
    }

    /**
     * @param       $team_name
     * @param array $members
     *
     * @return AgentTeam
     */
    protected function createAgentTeam($team_name, array $members)
    {
        $agent_team       = new AgentTeam();
        $agent_team->name = $team_name;

        foreach ($members as $member) {
            $agent_team->addPerson($member);
        }

        $this->getEm()->persist($agent_team);
        $this->getEm()->flush();

        return $agent_team;
    }

    protected function createCustomDataAndCustomFilters()
    {
        // CUSTOM DATA
        $question_def              = new CustomDefTicket();
        $question_def->title       = 'Favorite Color';
        $question_def->description = 'Pick your favorite color';
        $question_def->is_enabled  = $question_def->is_user_enabled  = true;

        $option_def_green              = new CustomDefTicket();
        $option_def_green->title       = 'Green';
        $option_def_green->description = 'Green';
        $option_def_green->is_enabled  = $option_def_green->is_enabled  = true;
        $option_def_green->parent      = $question_def;

        $option_def_blue              = new CustomDefTicket();
        $option_def_blue->title       = 'Blue';
        $option_def_blue->description = 'Blue';
        $option_def_blue->is_enabled  = $option_def_blue->is_enabled  = true;
        $option_def_blue->parent      = $question_def;

        $option_def_red              = new CustomDefTicket();
        $option_def_red->title       = 'Red';
        $option_def_red->description = 'Red';
        $option_def_red->is_enabled  = $option_def_red->is_enabled  = true;
        $option_def_red->parent      = $question_def;

        $this->getEm()->persist($question_def);
        $this->getEm()->persist($option_def_green);
        $this->getEm()->persist($option_def_blue);
        $this->getEm()->persist($option_def_red);
        $this->getEm()->flush();

        // TICKETS WITH CUSTOM DATA
        $ticket7             = $this->getEm()->getRepository(Ticket::class)->find(7);
        $ticket_data         = new CustomDataTicket();
        $ticket_data->field  = $question_def;
        $ticket_data->ticket = $ticket7;
        $ticket_data->value  = $option_def_red->getId();
        $this->getEm()->persist($ticket_data);

        $ticket2             = $this->getEm()->getRepository(Ticket::class)->find(2);
        $ticket_data         = new CustomDataTicket();
        $ticket_data->field  = $question_def;
        $ticket_data->ticket = $ticket2;
        $ticket_data->value  = $option_def_blue->getId();
        $this->getEm()->persist($ticket_data);

        $ticket4             = $this->getEm()->getRepository(Ticket::class)->find(4);
        $ticket_data         = new CustomDataTicket();
        $ticket_data->field  = $question_def;
        $ticket_data->ticket = $ticket4;
        $ticket_data->value  = $option_def_blue->getId();
        $this->getEm()->persist($ticket_data);

        $ticket11            = $this->getEm()->getRepository(Ticket::class)->find(11); // NOT "agent"
        $ticket_data         = new CustomDataTicket(); // NOT "agent"
        $ticket_data->field  = $question_def; // NOT "agent"
        $ticket_data->ticket = $ticket11; // NOT "agent"
        $ticket_data->value  = $option_def_red->getId(); // NOT "agent"
        $this->getEm()->persist($ticket_data);

        $this->getEm()->flush();

        // ADD CUSTOM FILTER

        // tickets that are assigned to the current user, whos custom color field is RED
        $term = new CompositeTerm([], TermInterface::OP_AND);
        $term->addTerm(
            new AgentTerm(
                [
                    'agent_ids' => [AgentTerm::ID_ME], // current agent
                ]
            )
        );
        $term->addTerm(
            new TicketCustomDataTerm(
                [
                    'field_id' => $question_def->getId(),
                    'values'   => [$option_def_red->getId()], // favorite color is RED
                ]
            )
        );

        $filter = new TicketFilter();
        $filter->setTitle('Fav. Color is Red');
        $filter->setTerm($term);
        $filter->setFilterSet($this->getEm()->getRepository(TicketFilterSet::class)->find(1));
        $this->getEm()->persist($filter);

        // tickets that are assigned to the current user, whos custom color field is RED
        $main_term = new CompositeTerm([], TermInterface::OP_AND);
        $main_term->addTerm(
            new AgentTerm(
                [
                    'agent_ids' => [AgentTerm::ID_ME], // current agent
                ]
            )
        );

        // NOTE: this is NOT the only way to do this, because the TicketCustomDataTerm
        //       "values" option allows for an array of ids. This is for testing embedded
        //       composite values.
        $embedded_or = new CompositeTerm([], TermInterface::OP_OR);
        $embedded_or->addTerm(
            new TicketCustomDataTerm(
                [
                    'field_id' => $question_def->getId(),
                    'values'   => [$option_def_red->getId()],
                    // favorite color is RED
                ]
            )
        );
        $embedded_or->addTerm(
            new TicketCustomDataTerm(
                [
                    'field_id' => $question_def->getId(),
                    'values'   => [$option_def_blue->getId()],
                    // favorite color is BLUE
                ]
            )
        );

        $main_term->addTerm(
            $embedded_or
        );

        $filter = new TicketFilter();
        $filter->setTitle('Fav. Color is Red or Blue');
        $filter->setTerm($main_term);
        $filter->setFilterSet($this->getEm()->getRepository(TicketFilterSet::class)->find(1));
        $this->getEm()->persist($filter);
    }
}
