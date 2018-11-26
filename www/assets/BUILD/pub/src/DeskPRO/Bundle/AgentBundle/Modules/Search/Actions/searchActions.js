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

export const loadCustomFields = createAction(
  'SEARCH_LOAD_CUSTOM_FIELDS',
  params => new Promise((resolve) => {
    repository('TicketCustomFields').search(params).then((promise) => {
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

export const loadSlas = createAction(
  'SEARCH_LOAD_SLAS',
  (params = {}) => new Promise((resolve) => {
    params.order_dir = 'asc';
    repository('Slas').search(params).then((promise) => {
      const res = promise.getData();

      resolve(res.data);
    });
  })
);
