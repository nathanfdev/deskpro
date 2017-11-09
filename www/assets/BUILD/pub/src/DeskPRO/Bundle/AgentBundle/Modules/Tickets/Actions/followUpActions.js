import { createAction } from 'DeskPRO/Component/Ampliflux';
import { repository } from 'DeskPRO/Bundle/AppBundle/DAL';

export const loadFollowUps = createAction(
  'TICKET_LOAD_FOLLOW_UP',
  ticketId => new Promise((resolve) => {
    repository('TicketFollowUp').loadAll(ticketId).then((promise) => {
      const res = promise.getData();

      resolve(res);
    });
  })
);
