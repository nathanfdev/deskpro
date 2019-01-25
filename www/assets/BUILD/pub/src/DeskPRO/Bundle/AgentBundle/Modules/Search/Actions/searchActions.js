import { createAction } from 'DeskPRO/Component/Ampliflux';
import { repository } from 'DeskPRO/Bundle/AppBundle/DAL';

export const loadProducts = createAction(
  'SEARCH_LOAD_PRODUCTS',
  params => new Promise((resolve) => {
    params.order_by = 'title';
    params.order_dir = 'asc';
    repository('TicketProducts').search(params).then((promise) => {
      const res = promise.getData();

      resolve(res.data);
    });
  })
);

export const loadCategories = createAction(
  'SEARCH_LOAD_CATEGORIES',
  params => new Promise((resolve) => {
    params.order_by = 'title';
    params.order_dir = 'asc';
    repository('TicketCategories').search(params).then((promise) => {
      const res = promise.getData();

      resolve(res.data);
    });
  })
);

export const loadPriorities = createAction(
  'SEARCH_LOAD_PRIORITIES',
  params => new Promise((resolve) => {
    params.order_by = 'title';
    params.order_dir = 'asc';
    repository('TicketPriorities').search(params).then((promise) => {
      const res = promise.getData();

      resolve(res.data);
    });
  })
);

export const loadWorkflows = createAction(
  'SEARCH_LOAD_WORKFLOWS',
  params => new Promise((resolve) => {
    params.order_by = 'title';
    params.order_dir = 'asc';
    repository('TicketWorkflows').search(params).then((promise) => {
      const res = promise.getData();

      resolve(res.data);
    });
  })
);

export const loadPersons = createAction(
  'SEARCH_LOAD_PERSONS',
  (params = {}) => new Promise((resolve) => {
    params.order_by = 'name';
    params.order_dir = 'asc';
    repository('Person').search(params).then((promise) => {
      const res = promise.getData();

      resolve(res.data);
    });
  })
);

export const loadOrganizations = createAction(
  'SEARCH_LOAD_ORGANIZATIONS',
  (params = {}) => new Promise((resolve) => {
    params.order_by = 'name';
    params.order_dir = 'asc';
    repository('Organization').search(params).then((promise) => {
      const res = promise.getData();

      resolve(res.data);
    });
  })
);

export const loadEmailAccounts = createAction(
  'SEARCH_LOAD_EMAIL_ACCOUNTS',
  (params = {}) => new Promise((resolve) => {
    params.order_by = 'address';
    params.order_dir = 'asc';
    repository('EmailAccount').search(params).then((promise) => {
      const res = promise.getData();

      resolve(res.data);
    });
  })
);

export const loadOrganizationLabels = createAction(
  'SEARCH_LOAD_ORGANIZATION_LABELS',
  (params = {}) => new Promise((resolve) => {
    params.order_by = 'label';
    params.order_dir = 'asc';
    repository('OrganizationLabels').search(params).then((promise) => {
      const res = promise.getData();
      console.log(res);

      resolve(res.data);
    });
  })
);

export const loadPersonLabels = createAction(
  'SEARCH_LOAD_PERSON_LABELS',
  (params = {}) => new Promise((resolve) => {
    params.order_by = 'label';
    params.order_dir = 'asc';
    repository('PersonLabels').search(params).then((promise) => {
      const res = promise.getData();

      resolve(res.data);
    });
  })
);

export const loadTicketLabels = createAction(
  'SEARCH_LOAD_TICKET_LABELS',
  (params = {}) => new Promise((resolve) => {
    params.order_by = 'label';
    params.order_dir = 'asc';
    repository('TicketLabels').search(params).then((promise) => {
      const res = promise.getData();

      resolve(res.data);
    });
  })
);

export const loadUserGroups = createAction(
  'SEARCH_LOAD_USER_GROUPS',
  (params = {}) => new Promise((resolve) => {
    params.order_by = 'title';
    params.order_dir = 'asc';
    repository('UserGroups').search(params).then((promise) => {
      const res = promise.getData();

      resolve(res.data);
    });
  })
);
