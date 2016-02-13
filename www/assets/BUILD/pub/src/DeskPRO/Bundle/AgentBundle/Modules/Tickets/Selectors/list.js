import { createSelector } from 'reselect';

const stateSelector = state => state.Tickets.list;

export const viewModeSelector = createSelector(
  stateSelector,
   state => state.get('viewMode')
);

export const listParamsSelector = createSelector(
  stateSelector,
    state => state.get('listParams')
);

export const tableVisibleFieldsSelector = createSelector(
  stateSelector,
    state => state.get('tableVisibleFields')
);

export const cardVisibleFieldsSelector = createSelector(
  stateSelector,
    state => state.get('cardVisibleFields')
);

export const listSortSelector = createSelector(
  listParamsSelector,
    params => params.get('sort')
);

export const listOrderSelector = createSelector(
  listParamsSelector,
    params => params.get('order')
);

export const paginationSelector = createSelector(
  stateSelector,
    state => state.get('pagination')
);
