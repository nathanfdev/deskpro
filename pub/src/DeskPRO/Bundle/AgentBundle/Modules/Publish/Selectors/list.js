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

export const listFiltersSelector = createSelector(
  [],
  () => {
    const filterSelector = [
      { label: 'Date', type: 'date', fromParam: 'created_from', toParam: 'created_to' }
    ];

    // Status options
    const statusOptions = [
      { label: 'Archived', value: 'archived' },
      { label: 'Published', value: 'published' },
      { label: 'Hidden', value: 'hidden' }
    ];
    filterSelector.push({
      label: 'Status', type: 'select', param: 'status', quickFilter: true,
      options: statusOptions
    });

    // Category options
    /* if (!currentListParams.get('navItem') || (!currentListParams.get('navItem').get('custom_category'))) {
      const categoryOptions = navState.get('customCategories').toJS().map(cat => ({
        label: cat.title,
        value: cat.title
      }));
      filterSelector.push({
        label: 'Category', type: 'select', param: 'custom_category', quickFilter: true,
        options: categoryOptions
      });
    }*/

    return filterSelector;
  }
);


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
