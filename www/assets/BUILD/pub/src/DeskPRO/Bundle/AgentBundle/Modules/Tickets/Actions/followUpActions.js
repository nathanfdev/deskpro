import { createAction } from 'DeskPRO/Component/Ampliflux';
import { repository } from 'DeskPRO/Bundle/AppBundle/DAL';

export const loadFollowUps = createAction(
  'TICKET_LOAD_FOLLOW_UP',
  (ticketId, params) => new Promise((resolve) => {
    repository('Ticket').loadAllFollowUps(ticketId, params).then((promise) => {
      const res = promise.getData();

      resolve(res);
    });
  })
);

export const createFollowUp = createAction(
  'TICKET_SAVE_FOLLOW_UP',
  (ticketId, data) => new Promise((resolve, reject) => {
    repository('Ticket').createFollowUp(ticketId, data).then((promise) => {
      const res = promise.getData();
      resolve(res.data);
    }, (error) => {
      reject(error);
    });
  })
);

export const deleteFollowUp = createAction(
  'TICKET_DELETE_FOLLOW_UP',
  (ticketId, followUpId) => new Promise((resolve) => {
    repository('Ticket').deleteFollowUp(ticketId, followUpId).then((promise) => {
      const res = promise.getData();
      resolve(res.data);
    });
  })
);
