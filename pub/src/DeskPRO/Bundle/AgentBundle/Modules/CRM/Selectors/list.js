import { createSelector } from 'reselect';
import { hashStateSelectorFactory } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Selectors/routing';

const stateSelector = state => state.CRM.list;

export const currentViewModeSelector = hashStateSelectorFactory(['list', 'view'], 'card');

export const currentListParamsSelector = createSelector(
  stateSelector,
    state => state.get('currentListParams')
);

export const organizationsSelector = createSelector(
  stateSelector,
    state => state.get('organizations')
);

export const peopleSelector = createSelector(
  stateSelector,
    state => state.get('people')
);

export const currentListSortSelector = createSelector(
  currentListParamsSelector,
    params => params.get('sort')
);

export const currentListOrderSelector = createSelector(
  currentListParamsSelector,
    params => params.get('order')
);

export const currentContentSelector = createSelector(
  currentListParamsSelector,
    params => params.get('content')
);
