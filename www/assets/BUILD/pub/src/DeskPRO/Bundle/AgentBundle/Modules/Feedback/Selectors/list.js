import { createSelector } from 'reselect';
import { hashStateSelectorFactory } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Selectors/routing';


const stateSelector = state => state.Feedback.list;

export const idsSelector = createSelector(
  stateSelector,
  state => state.get('elements')
);

export const currentListParamsSelector = createSelector(
  stateSelector,
  state => state.get('currentListParams')
);

export const visibleFieldsSelector = createSelector(
  stateSelector,
  state => state.get('visibleFields')
);

export const cardVisibleFieldsSelector = createSelector(
  visibleFieldsSelector,
  params => params.get('card')
);

export const tableVisibleFieldsSelector = createSelector(
  visibleFieldsSelector,
  params => params.get('table')
);

export const currentListOrderBySelector  = createSelector(
  currentListParamsSelector,
  params => params.get('order_by')
);
export const currentListOrderDirSelector = createSelector(
  currentListParamsSelector,
  params => params.get('order_dir')
);

export const isCommentsSelector = createSelector(
  currentListParamsSelector,
  params => params.get('isComments')
);

export const navItemSelector = createSelector(
  currentListParamsSelector,
  list => list.get('navItem')
);

export const paginationSelector = createSelector(
  stateSelector,
  list => list.get('pagination')
);

export const isLoadedSelector = createSelector(
  stateSelector,
  list => list.getIn(['async', 'done'])
);

export const currentViewModeSelector = hashStateSelectorFactory(['list', 'view'], 'card');
