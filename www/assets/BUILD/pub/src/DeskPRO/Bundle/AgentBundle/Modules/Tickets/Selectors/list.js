import { createSelector } from 'reselect';

const stateSelector = state => state.Tickets.list;

export const elementsSelector = createSelector(
  stateSelector,
  state => state.get('elements')
);

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

export const listOrderBySelector = createSelector(
  listParamsSelector,
    params => params.get('order_by')
);

export const listOrderDirSelector = createSelector(
  listParamsSelector,
    params => params.get('order_dir')
);

export const paginationSelector = createSelector(
  stateSelector,
    state => state.get('pagination')
);
