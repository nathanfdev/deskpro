import { createAction } from 'DeskPRO/Component/Ampliflux';
import { repository } from 'DeskPRO/Bundle/AppBundle/DAL';

export const loadApprovalRequests = createAction(
  'TICKET_LOAD_APPROVAL_REQUESTS',
  (ticketId, params) => new Promise((resolve) => {
    repository('Ticket').loadApprovals(ticketId, params).then((promise) => {
      const res = promise.getData();

      resolve(res);
    });
  })
);

export const loadApprovalRequest = createAction(
  'TICKET_LOAD_APPROVAL_REQUEST',
  (ticketId, params) => new Promise((resolve) => {
    repository('Ticket').loadApprovals(ticketId, params).then((promise) => {
      const res = promise.getData();

      resolve(res);
    });
  })
);

export const loadApproversList = createAction(
  'TICKET_LOAD_APPROVERS_LIST',
  (ids, params) => new Promise((resolve) => {
    repository('Person').loadBatch(ids, params).then((promise) => {
      const res = promise.getData();

      resolve(res);
    });
  })
);

export const loadOrganizationManagers = createAction(
  'TICKET_LOAD_ORGANIZATION_MANAGERS',
  orgId => new Promise((resolve) => {
    repository('Person').search({ organization: orgId, organization_manager: true }).then((promise) => {
      const res = promise.getData();

      resolve(res);
    });
  })
);

export const loadApprovalResponses = createAction(
  'TICKET_LOAD_APPROVAL_RESPONSES',
  approvalId => new Promise((resolve) => {
    repository('TicketApprovalResponse').getAll(approvalId).then((promise) => {
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
  approvalRequestId => new Promise((resolve) => {
    repository('TicketApproval').cancelApprovalRequest(approvalRequestId).then(() => {
      resolve();
    });
  })
);

export const acceptApprovalRequest = createAction(
  'TICKET_ACCEPT_APPROVAL_REQUEST',
  (approvalRequestId, data) => new Promise((resolve) => {
    repository('TicketApproval').acceptApprovalRequest(approvalRequestId, data).then((promise) => {
      const res = promise.getData();
      resolve(res.data);
    });
  })
);

export const rejectApprovalRequest = createAction(
  'TICKET_REJECT_APPROVAL_REQUEST',
  (approvalRequestId, data) => new Promise((resolve) => {
    repository('TicketApproval').rejectApprovalRequest(approvalRequestId, data).then((promise) => {
      const res = promise.getData();
      resolve(res.data);
    });
  })
);
