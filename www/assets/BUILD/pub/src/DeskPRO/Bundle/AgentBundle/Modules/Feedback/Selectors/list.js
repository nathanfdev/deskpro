import { createSelector } from 'reselect';
import { hashStateSelectorFactory } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Selectors/routing';
import { feedbackLabelsSelector } from './nav';
import { collectionSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';


const stateSelector = state => state.Feedback.list;
const navStateSelector = state => state.Feedback.nav;

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

export const currentListSortSelector = createSelector(
  currentListParamsSelector,
    params => params.get('sort')
);

export const currentListOrderSelector = createSelector(
  currentListParamsSelector,
    params => params.get('order')
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

export const listFiltersSelector = createSelector(
  [navStateSelector, currentListParamsSelector, collectionSelectorFactory('Feedback', 'feedback'), feedbackLabelsSelector, collectionSelectorFactory('FeedbackType', 'feedback')],
  (navState, currentListParams, categories, labels, types) => {
    const checkIfShowStatus = ()=> {
      const navItem = currentListParams.get('navItem');
      return !navItem || (!navItem.get('status') && !navItem.get('status_category') && !navItem.get('hidden_status'));
    };
    const filterSelector = [
      { label: 'Date', type: 'date', fromParam: 'created_from', toParam: 'created_to' }
    ];

    // Type options
    if (!currentListParams.get('navItem') || !currentListParams.get('navItem').get('category')) {
      const typeOptions = types.toArray().map(type => ({ value: type.get('title'), label: type.get('title') }));
      filterSelector.push({
        label: 'Type',
        type: 'select',
        param: 'category',
        quickFilter: true,
        options: typeOptions
      });
    }

    // Status options
    if (checkIfShowStatus()) {
      const statuses = navState.get('statuses').toJS();
      const toStatusOptions = (nested, param) => (nested || []).map(opt => ({
        value: opt.id,
        label: opt.title,
        param: param
      }));
      const statusOptions = [
        { label: 'Active', value: 'active', nested: toStatusOptions(statuses.active.nested, 'status_category') },
        { label: 'Closed', value: 'closed', nested: toStatusOptions(statuses.closed.nested, 'status_category') },
        { label: 'Hidden', value: 'hidden', nested: toStatusOptions(statuses.hidden.nested, 'hidden_status') }
      ];
      filterSelector.push({
        label: 'Status', type: 'select', param: 'status', quickFilter: true,
        options: statusOptions
      });
    }

    // Category options
    if (!currentListParams.get('navItem') || (!currentListParams.get('navItem').get('custom_category'))) {
      const categoryOptions = categories.toArray().map(cat => ({
        label: cat.get('input'),
        value: cat.get('input')
      }));
      filterSelector.push({
        label: 'Category', type: 'select', param: 'custom_category', quickFilter: true,
        options: categoryOptions
      });
    }

    // Labels options
    if (!currentListParams.get('navItem') || !currentListParams.get('navItem').get('label')) {
      filterSelector.push({
        label: 'Labels',
        type: 'labels',
        param: 'label',
        modeParam: 'labels_mode',
        labels: labels
      });
    }
    return filterSelector;
  }
);

/* ==================== Mass actions ===================== */

export const massActionsSelector = createSelector(
  [navStateSelector, collectionSelectorFactory('Feedback', 'feedback'), collectionSelectorFactory('FeedbackType', 'feedback'), feedbackLabelsSelector],
  (navState, categories, types, labels) => {
    const massActions = [];
    // Type options
    const typeOptions = types.toArray().map(type => ({ value: type.get('id'), label: type.get('title') }));
    massActions.push({
      label: 'Type',
      type: 'action',
      param: 'set_type',
      quickFilter: true,
      options: typeOptions
    });

    // Status options
    const statuses = navState.get('statuses').toJS();
    const toStatusOptions = (nested, param) => (nested || []).map(opt => ({
      value: opt.id,
      label: opt.title,
      param: param
    }));
    const statusOptions = [
      { label: 'Active', value: 'active', nested: toStatusOptions(statuses.active.nested, 'set_status_category') },
      { label: 'Closed', value: 'closed', nested: toStatusOptions(statuses.closed.nested, 'set_status_category') },
      { label: 'Hidden', value: 'hidden', nested: toStatusOptions(statuses.hidden.nested, 'set_hidden_status') }
    ];
    massActions.push({
      label: 'Status', type: 'action', param: 'set_status', quickFilter: true,
      options: statusOptions
    });

    // Category options
    const categoryOptions = categories.toArray().map(cat => ({
      label: cat.get('input'),
      value: cat.get('input')
    }));
    massActions.push({
      label: 'Category', type: 'action', param: 'set_category', quickFilter: true,
      options: categoryOptions
    });

    // Other options
    const otherOptions = [
      { label: 'Add label', icon: 'plus-square', labels: labels, param: 'add_labels' },
      { label: 'Remove label', icon: 'minus-square', labels: labels, param: 'remove_labels' }
    ];
    massActions.push({ icon: 'fa-asterisk', type: 'menu', param: 'other', options: otherOptions });

    return massActions;
  }
);