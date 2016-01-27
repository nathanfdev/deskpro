import { createSelector } from 'reselect';
import { hashStateSelectorFactory } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Selectors/routing';

const stateSelector = state => state.Chat.list;

export const currentListParamsSelector = createSelector(
  stateSelector,
  list => list.get('currentListParams')
);

export const listSortSelector = createSelector(
  currentListParamsSelector,
    params => params.get('sort')
);

export const listOrderSelector = createSelector(
  currentListParamsSelector,
    params => params.get('order')
);

export const elementsSelector = createSelector(
  stateSelector,
  list => list.get('elements')
);

export const paginationSelector = createSelector(
  stateSelector,
  list => list.get('pagination')
);

export const isLoadedSelector = createSelector(
  stateSelector,
    list => list.getIn(['async', 'done'])
);

export const viewModeSelector = hashStateSelectorFactory(['list', 'view'], 'card');
