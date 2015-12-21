import { createSelector } from 'reselect';
import { hashStateSelectorFactory } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Selectors/routing';
import { createPeopleRequestSelectors }
  from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Selectors/peopleSelectors';

const stateSelector = state => state.Publish.list;

export const articlesSelector = createSelector(
  stateSelector,
    state => state.get('articles')
);

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

export const massActionsSelector = createSelector(
  [],
  () => {
    const massActions = [];
    massActions.push({
      label: 'Example',
      type: 'action',
      param: 'example',
      quickFilter: true,
      options: [{ value: 1, label: 'Example1' }, { value: 2, label: 'Example2' }]
    });
    return massActions;
  }
);
