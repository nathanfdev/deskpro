<?php


namespace Application\DeskPRO\JobQueue\Processor;

use Application\DeskPRO\App;
use Application\DeskPRO\Tickets\Util as TicketUtil;
use Doctrine\DBAL\Connection;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PurgeTicketsProcessor extends AbstractJobProcessor
{
    const JOB_TYPE = 'purge_tickets';

    /**
     * Constructor.
     *
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        parent::__construct($connection);
    }

    /**
     * {@inheritDoc}
     */
    public function process(array $data, array $job)
    {
        if ($data['type'] === 'deleted') {
            $statusId = (int) App::getContainer()->getTicketStatuses()->getDeletedStatus()->getId();
        } else {
            $statusId = (int) App::getContainer()->getTicketStatuses()->getSpamStatus()->getId();
        }

        try {
            $allTickets = App::getDb()->fetchAll('
                SELECT id, person_id
                FROM tickets
                WHERE tickets.ticket_status_id = ?
                LIMIT 1000
            ', [$statusId]);

            $dateStr = date('Y-m-d H:i:s');

            foreach ($allTickets as $ticket) {
                $this->connection->beginTransaction();

                try {
                    TicketUtil::deleteTicketAttachments($ticket['id'], $this->connection);

                    if ($data['type'] === 'spam') {
                        $this->connection->delete('tickets_deleted', ['ticket_id' => $ticket['id']]);
                        $this->connection->replace('tickets_deleted', [
                            'ticket_id'     => $ticket['id'],
                            'by_person_id'  => null,
                            'new_ticket_id' => 0,
                            'date_created'  => $dateStr,
                            'reason'        => 'Deleted as spam (system cleanup)',
                        ]);
                    }

                    $this->connection->delete('tickets', ['id' => $ticket['id']]);
                    $this->connection->delete('tickets_search_active', ['id' => $ticket['id']]);

                    $this->connection->commit();
                } catch (\Exception $e) {
                    $this->connection->rollback();
                    throw $e;
                }
            }

            $this->runSuccessHandler($job);
        } catch (\Exception $e) {
            $this->runExceptionHandler($job, $e);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setRequired(['type']);
        $resolver->setAllowedValues('type', ['spam', 'deleted']);
    }
}
