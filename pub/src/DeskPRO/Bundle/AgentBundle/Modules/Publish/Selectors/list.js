import { createSelector } from 'reselect';
import { hashStateSelectorFactory } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Selectors/routing';
import { createPeopleRequestSelectors }
  from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Selectors/peopleSelectors';

const stateSelector = state => state.Publish.list;

export const currentListParamsSelector = createSelector(
  stateSelector,
    state => state.get('currentListParams')
);


export const peopleSelector = createSelector(
  createPeopleRequestSelectors('publish').recordsSel,
    people => people
);


export const currentListSortSelector = createSelector(
  currentListParamsSelector,
    params => params.get('sort')
);

export const currentListOrderSelector = createSelector(
  currentListParamsSelector,
    params => params.get('order')
);

export const currentViewModeSelector = hashStateSelectorFactory(['list', 'view'], 'card');
