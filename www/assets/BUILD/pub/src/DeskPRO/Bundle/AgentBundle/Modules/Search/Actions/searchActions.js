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
