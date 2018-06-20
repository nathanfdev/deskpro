import { createAction } from 'DeskPRO/Component/Ampliflux';
import { repository } from 'DeskPRO/Bundle/AppBundle/DAL';

export const loadProducts = createAction(
  'SEARCH_LOAD_PRODUCTS',
  params => new Promise((resolve) => {
    repository('TicketProducts').loadAll(params).then((promise) => {
      const res = promise.getData();

      resolve(res.data);
    });
  })
);

export const loadCategories = createAction(
  'SEARCH_LOAD_CATEGORIES',
  params => new Promise((resolve) => {
    repository('TicketCategories').loadAll(params).then((promise) => {
      const res = promise.getData();

      resolve(res.data);
    });
  })
);

export const loadPriorities = createAction(
  'SEARCH_LOAD_PRIORITIES',
  params => new Promise((resolve) => {
    repository('TicketPriorities').loadAll(params).then((promise) => {
      const res = promise.getData();

      resolve(res.data);
    });
  })
);

export const loadWorkflows = createAction(
  'SEARCH_LOAD_WORKFLOWS',
  params => new Promise((resolve) => {
    repository('TicketWorkflows').loadAll(params).then((promise) => {
      const res = promise.getData();

      resolve(res.data);
    });
  })
);

export const loadCustomFields = createAction(
  'SEARCH_LOAD_CUSTOM_FIELDS',
  params => new Promise((resolve) => {
    repository('TicketCustomFields').loadAll(params).then((promise) => {
      const res = promise.getData();

      resolve(res.data);
    });
  })
);
