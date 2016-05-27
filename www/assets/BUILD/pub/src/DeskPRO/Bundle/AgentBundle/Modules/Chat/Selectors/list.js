import { createSelector } from 'reselect';
import { hashStateSelectorFactory } from '../../Application/Selectors/routing';
import { constants } from '../../../Constants/Constants';

const stateSelector = state => state.Chat.list;

export const currentListParamsSelector = createSelector(
  stateSelector,
  list => list.get('currentListParams')
);

export const listOrderBySelector = createSelector(
  currentListParamsSelector,
    params => params.get('order_by')
);

export const listOrderDirSelector = createSelector(
  currentListParamsSelector,
    params => params.get('order_dir')
);

export const paginationSelector = createSelector(
  stateSelector,
  list => list.get('pagination')
);

export const elementsSelector = createSelector(
  stateSelector,
  list => list.get('elements')
);

export const tableFieldsSelector = createSelector(
  stateSelector,
  state => state.getIn(['fields', constants.VIEW_MODE_TABLE])
);

export const cardFieldsSelector = createSelector(
  stateSelector,
  state => state.getIn(['fields', constants.VIEW_MODE_CARD])
);


export const viewModeSelector = hashStateSelectorFactory(['list', 'view'], 'card');

export const isLoadedSelector = createSelector(
  stateSelector,
  list => list.getIn(['async', 'done'])
);
