import { createSelector } from 'reselect';
import { hashStateSelectorFactory } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Selectors/routing';
import { feedbackCategoriesSelector, feedbackTypesSelector } from './recordStores';
import { feedbackLabelsSelector } from './nav';

const stateSelector = state => state.Feedback.list;
const navStateSelector = state => state.Feedback.nav;

export const currentListParamsSelector = createSelector(
  stateSelector,
    state => state.get('currentListParams')
);

export const currentViewFieldsParamsSelector = createSelector(
  stateSelector,
  (state) => {
    const cardVisibleFields = state.get('cardVisibleFields');
    const tableVisibleFields = state.get('tableVisibleFields');
    return { cardVisibleFields: cardVisibleFields, tableVisibleFields: tableVisibleFields };
  }
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

export const currentViewModeSelector = hashStateSelectorFactory(['list', 'view'], 'card');

export const tableVisibleFieldsSelector = createSelector(
  stateSelector,
    state => state.get('tableVisibleFields')
);

export const cardVisibleFieldsSelector = createSelector(
  stateSelector,
    state => state.get('cardVisibleFields')
);


export const listFiltersSelector = createSelector(
  [navStateSelector, currentListParamsSelector, feedbackCategoriesSelector, feedbackLabelsSelector, feedbackTypesSelector],
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
        value: opt.title,
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
      const categoryOptions = categories.map(cat => ({
        label: cat.title,
        value: cat.title
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

export const massActionsParamsSelector = createSelector(
  stateSelector,
    state => state.get('massActions')
);

export const massActionsSelector = createSelector(
  [navStateSelector, feedbackCategoriesSelector, feedbackTypesSelector, feedbackLabelsSelector],
  (navState, categories, types, labels) => {
    const massActions = [];
    // Type options
    const typeOptions = types.toArray().map(type => ({ value: type.get('id'), label: type.get('title') }));
    massActions.push({
      label: 'Type',
      type: 'action',
      param: 'category',
      quickFilter: true,
      options: typeOptions
    });

    // Status options
    const statuses = navState.get('statuses').toJS();
    const toStatusOptions = (nested, param) => (nested || []).map(opt => ({
      value: opt.title,
      label: opt.title,
      param: param
    }));
    const statusOptions = [
      { label: 'Active', value: 'active', nested: toStatusOptions(statuses.active.nested, 'status_category') },
      { label: 'Closed', value: 'closed', nested: toStatusOptions(statuses.closed.nested, 'status_category') },
      { label: 'Hidden', value: 'hidden', nested: toStatusOptions(statuses.hidden.nested, 'hidden_status') }
    ];
    massActions.push({
      label: 'Status', type: 'action', param: 'status', quickFilter: true,
      options: statusOptions
    });

    // Category options
    const categoryOptions = navState.get('customCategories').toJS().map(cat => ({
      label: cat.title,
      value: cat.title
    }));
    massActions.push({
      label: 'Category', type: 'action', param: 'custom_category', quickFilter: true,
      options: categoryOptions
    });

    // Other options
    const otherOptions = [
      { label: 'Add label', icon: 'plus-square', labels: labels, param: 'addLabels' },
      { label: 'Remove label', icon: 'minus-square', labels: labels, param: 'removeLabels' }
    ];
    massActions.push({ icon: 'fa-asterisk', type: 'menu', param: 'other', options: otherOptions });

    return massActions;
  }
);