import { createSelector } from 'reselect';
import { createPeopleRequestSelectors }
  from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Selectors/peopleSelectors';
import { createEmailsRequestSelectors }
  from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Selectors/emailsSelectors';
import { createFeedbackTypesRequestSelectors } from '../RecordStores/Selectors/feedbackTypesSelectors';
import { createFeedbackCommentsRequestSelectors } from '../RecordStores/Selectors/feedbackCommentsSelectors';
import { createFeedbackStatusesRequestSelectors } from '../RecordStores/Selectors/feedbackStatusesSelectors';
import { createFeedbackCategoriesRequestSelectors } from '../RecordStores/Selectors/feedbackCategoriesSelectors';
import { createFeedbackRequestSelectors } from '../RecordStores/Selectors/feedbackSelectors';
import { hashStateSelectorFactory } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Selectors/routing';

const stateSelector = state => state.Feedback.list;
const navStateSelector = state => state.Feedback.nav;

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

export const isCommentsSelector = createSelector(
  stateSelector,
    list => list.get('currentListParams').get('isComments')
);

export const peopleSelector = createSelector(
  createPeopleRequestSelectors('feedback').recordsSel,
    people => people
);

export const emailsSelector = createSelector(
  createEmailsRequestSelectors('feedback').recordsSel,
    email => email
);

export const feedbackTypesSelector = createSelector(
  createFeedbackTypesRequestSelectors('all').recordsSel,
    types => types
);

export const feedbackLabelsSelector = createSelector(
  navStateSelector,
    state => state.get('labels')
);

export const feedbackCommentsSelector = createSelector(
  createFeedbackCommentsRequestSelectors('feedback').recordsSel,
    comments => comments
);

export const feedbackStatusesSelector = createSelector(
  createFeedbackStatusesRequestSelectors('feedback').recordsSel,
    statuses => statuses
);

export const feedbackCategoriesSelector = createSelector(
  createFeedbackCategoriesRequestSelectors('feedback').recordsSel,
    categories => categories
);

export const feedbackSelector = createSelector(
  createFeedbackRequestSelectors('feedback').recordsSel,
    feedback => feedback
);

export const currentViewModeSelector = hashStateSelectorFactory(['list', 'view'], 'card');

export const listFiltersSelector = createSelector(
  [navStateSelector, currentListParamsSelector, feedbackCategoriesSelector, feedbackLabelsSelector, feedbackTypesSelector],
  (navState, currentListParams, categories, labels, types) => {
    const filterSelector = [
      { label: 'Date', type: 'date', fromParam: 'created_from', toParam: 'created_to' },
      { label: 'Labels', type: 'labels', param: 'labels', modeParam: 'labels_mode', labels: labels }
    ];
    if (!currentListParams.get('navItem') || !currentListParams.get('navItem').get('category')) {
      // Type options
      const typeOptions = types.toArray().map(type => ({ value: type.get('id'), label: type.get('title') }));
      filterSelector.push({ label: 'Type', type: 'select', param: 'type', options: typeOptions });
    }
    if (!currentListParams.get('navItem') || (!currentListParams.get('navItem').get('status') && !currentListParams.get('navItem').get('status_category'))) {
      // Status options
      const statuses = navState.get('statuses').toJS();
      const toStatusOptions = nested => (nested || []).map(opt => ({ value: opt.group, label: opt.group }));
      const statusOptions = [
        { label: 'New', value: 'new', nested: toStatusOptions(statuses.new.nested) },
        { label: 'Active', value: 'active', nested: toStatusOptions(statuses.active.nested) },
        { label: 'Closed', value: 'closed', nested: toStatusOptions(statuses.closed.nested) },
        { label: 'Hidden', value: 'hidden', nested: toStatusOptions(statuses.hidden.nested) }
      ];
      filterSelector.push({ label: 'Status', type: 'select', param: 'status', options: statusOptions });
    }
    if (!currentListParams.get('navItem') || (!currentListParams.get('navItem').get('custom_category'))) {
      // Category options
      const categoryOptions = navState.get('customCategories').toJS().map(cat => ({
        label: cat.group,
        value: cat.group
      }));
      filterSelector.push({ label: 'Category', type: 'select', param: 'category', options: categoryOptions });
    }
    return filterSelector;
  }
);