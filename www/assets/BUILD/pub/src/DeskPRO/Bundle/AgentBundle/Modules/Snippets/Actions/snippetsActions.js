import { createAction } from 'DeskPRO/Component/Ampliflux';
import { repository } from 'DeskPRO/Bundle/AppBundle/DAL';

export const loadTicketSnippets = createAction(
  'SNIPPETS_LOAD_TICKET_SNIPPETS',
  () => new Promise((resolve) => {
    repository('TicketSnippets').loadTicketSnippets().then((promise) => {
      resolve(promise.data.data);
    });
  })
);
