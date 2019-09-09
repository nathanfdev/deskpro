import { createAction } from 'DeskPRO/Component/Ampliflux';
import { repository } from 'DeskPRO/Bundle/AppBundle/DAL';

export const loadApprovals = createAction(
  'TICKET_LOAD_APPROVALS',
  (ticketId, params) => new Promise((resolve) => {
    repository('Ticket').loadApprovals(ticketId, params).then((promise) => {
      const res = promise.getData();

      resolve(res);
    });
  })
);

export const loadApprovalTemplates = createAction(
  'TICKET_LOAD_APPROVAL_TEMPLATES',
  () => new Promise((resolve) => {
    repository('Ticket').loadApprovalTemplates().then((promise) => {
      const res = promise.getData();

      resolve(res);
    });
  })
);

export const createApprovalRequest = createAction(
  'TICKET_CREATE_APPROVAL_REQUEST',
  (ticketId, data) => new Promise((resolve, reject) => {
    repository('Ticket').createApprovalRequest(ticketId, data).then((promise) => {
      const res = promise.getData();
      resolve(res.data);
    }, (error) => {
      reject(error);
    });
  })
);

export const cancelApprovalRequest = createAction(
  'TICKET_CANCEL_APPROVAL_REQUEST',
  (ticketId, approvalRequestId) => new Promise((resolve) => {
    repository('Ticket').cancelApprovalRequest(ticketId, approvalRequestId).then((promise) => {
      const res = promise.getData();
      resolve(res.data);
    });
  })
);

export const acceptApprovalRequest = createAction(
  'TICKET_ACCEPT_APPROVAL_REQUEST',
  (ticketId, approvalRequestId) => new Promise((resolve) => {
    repository('Ticket').acceptApprovalRequest(ticketId, approvalRequestId).then((promise) => {
      const res = promise.getData();
      resolve(res.data);
    });
  })
);

export const rejectApprovalRequest = createAction(
  'TICKET_REJECT_APPROVAL_REQUEST',
  (ticketId, approvalRequestId) => new Promise((resolve) => {
    repository('Ticket').rejectApprovalRequest(ticketId, approvalRequestId).then((promise) => {
      const res = promise.getData();
      resolve(res.data);
    });
  })
);
