import { TicketFilterRepository } from './Repositories/TicketFilterRepository';

export const repositoriesConfig = {
  Ticket: {
    type: 'api',
    url: '/tickets',
    search: ['filter', 'label', 'status', 'agent', 'person', 'organization', 'problem', 'department', 'urgency']
  },
  TicketFilter: {type: 'api', url: '/ticket_filters', repositoryClass: TicketFilterRepository}
};
