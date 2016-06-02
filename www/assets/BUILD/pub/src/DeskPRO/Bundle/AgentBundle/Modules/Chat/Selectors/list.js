import { createSelector } from 'reselect';
import { hashStateSelectorFactory } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Selectors/routing';

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

export const viewModeSelector = hashStateSelectorFactory(['list', 'view'], 'card');

export const isLoadedSelector = createSelector(
  stateSelector,
  list => list.getIn(['async', 'done'])
);
