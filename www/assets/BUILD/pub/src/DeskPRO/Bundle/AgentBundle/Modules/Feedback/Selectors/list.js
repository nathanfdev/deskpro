import { createSelector } from 'reselect';
import { hashStateSelectorFactory } from '../../../Modules/Application/Selectors/routing';
import { constants } from '../../../Constants/Constants';


const stateSelector = state => state.Feedback.list;

export const idsSelector = createSelector(
  stateSelector,
  state => state.get('elements')
);

export const currentListParamsSelector = createSelector(
  stateSelector,
  state => state.get('currentListParams')
);

export const tableFieldsSelector = createSelector(
  stateSelector,
  state => state.getIn(['fields', constants.VIEW_MODE_TABLE])
);

export const cardFieldsSelector = createSelector(
  stateSelector,
  state => state.getIn(['fields', constants.VIEW_MODE_CARD])
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
