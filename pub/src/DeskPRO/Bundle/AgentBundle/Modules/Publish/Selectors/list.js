import { createSelector } from 'reselect';
import { hashStateSelectorFactory } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Selectors/routing';

const stateSelector = state => state.Publish.list;
const navStateSelector = state => state.Publish.nav;

export const contentSelector = createSelector(
  stateSelector,
    state => state.get('currentListParams').get('content')
);


export const paginationSelector = createSelector(
  stateSelector,
    list => list.get('pagination')
);

export const loadedSelector = createSelector(
  stateSelector,
    list => list.getIn(['async', 'done'])
);

export const elementsSelector = createSelector(
  stateSelector,
    state => state.get('elements')
);

export const currentListParamsSelector = createSelector(
  stateSelector,
    state => state.get('currentListParams')
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
  [navStateSelector, currentListParamsSelector],
  (navState, currentListParams) => {
    const filterSelector = [
      { label: 'Date', type: 'datePeriod', param: 'date_filter' }
    ];

    // Status options
    const statusOptions = [
      { label: 'Archived', value: 'archived' },
      { label: 'Published', value: 'published' },
      {
        label: 'Hidden',
        value: 'hidden',
        nested: [
          { value: 'unpublished', label: 'Unpublished', param: 'hidden_status' },
          { value: 'deleted', label: 'Deleted', param: 'hidden_status' },
          { value: 'spam', label: 'Spam', param: 'hidden_status' },
          { value: 'draft', label: 'Draft', param: 'hidden_status' }
        ]
      }
    ];
    filterSelector.push({
      label: 'Status', type: 'select', param: 'status', quickFilter: true,
      options: statusOptions
    });
    const content = currentListParams.get('content');
    if (['articles', 'news', 'downloads'].indexOf(content) > -1) { // Temporary, until full understanding of comments functionality
      // Category options
      if ((!currentListParams.get('navItem') || !currentListParams.get('navItem').get('category')) && navState.get('categories')) {
        const categoryOptions = navState.get('categories').get(content).toJS()
          .map(cat => ({
            label: cat.title,
            value: cat.id
          }));
        filterSelector.push({
          label: 'Category', type: 'select', param: 'category', quickFilter: true,
          options: categoryOptions
        });
      }
    }
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
