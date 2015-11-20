import { createSelector } from 'reselect';
import { reduceImmutableToProperty } from 'DeskPRO/Component/Util/Map';
import { createPeopleRequestSelectors }
  from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Selectors/peopleSelectors';
import { createFeedbackTypesRequestSelectors } from '../RecordStores/Selectors/feedbackTypesSelectors';
import { createFeedbackCommentsRequestSelectors } from '../RecordStores/Selectors/feedbackCommentsSelectors';
import { createFeedbackCategoriesRequestSelectors } from '../RecordStores/Selectors/feedbackCategoriesSelectors';
import { createFeedbackStatusCategoriesRequestSelectors } from '../RecordStores/Selectors/feedbackStatusCategoriesSelectors';
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

export const feedbackTypesSelector = createSelector(
  createFeedbackTypesRequestSelectors('feedback').recordsSel,
    types => types
);

export const feedbackLabelsSelector = createSelector(
  navStateSelector,
    state => reduceImmutableToProperty('label', state.get('labels'))
);

export const feedbackCommentsSelector = createSelector(
  createFeedbackCommentsRequestSelectors('feedback').recordsSel,
    comments => comments
);

export const feedbackCategoriesSelector = createSelector(
  createFeedbackCategoriesRequestSelectors('feedback').recordsSel,
    categories => categories
);

export const feedbackStatusCategoriesSelector = createSelector(
  createFeedbackStatusCategoriesRequestSelectors('feedback').recordsSel,
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
    const checkIfShowStatus = ()=> {
      return !currentListParams.get('navItem') ||
        (!currentListParams.get('navItem').get('status') && !currentListParams.get('navItem').get('status_category') && !currentListParams.get('navItem').get('hidden_status'));
    };
    const filterSelector = [
      { label: 'Date', type: 'date', fromParam: 'created_from', toParam: 'created_to' }
    ];
    if (!currentListParams.get('navItem') || !currentListParams.get('navItem').get('category')) {
      // Type options
      const typeOptions = types.toArray().map(type => ({ value: type.get('title'), label: type.get('title') }));
      filterSelector.push({ label: 'Type', type: 'select', param: 'category', options: typeOptions });
    }
    if (checkIfShowStatus()) {
      // Status options
      const statuses = navState.get('statuses').toJS();
      const toStatusOptions = (nested, param) => (nested || []).map(opt => ({
        value: opt.group,
        label: opt.group,
        param: param
      }));
      const statusOptions = [
        { label: 'New', value: 'new', nested: toStatusOptions(statuses.new.nested) },
        { label: 'Active', value: 'active', nested: toStatusOptions(statuses.active.nested, 'status_category') },
        { label: 'Closed', value: 'closed', nested: toStatusOptions(statuses.closed.nested, 'status_category') },
        { label: 'Hidden', value: 'hidden', nested: toStatusOptions(statuses.hidden.nested, 'hidden_status') }
      ];
      filterSelector.push({ label: 'Status', type: 'select', param: 'status', options: statusOptions });
    }
    if (!currentListParams.get('navItem') || (!currentListParams.get('navItem').get('custom_category'))) {
      // Category options
      const categoryOptions = navState.get('customCategories').toJS().map(cat => ({
        label: cat.group,
        value: cat.group
      }));
      filterSelector.push({ label: 'Category', type: 'select', param: 'custom_category', options: categoryOptions });
    }
    if (!currentListParams.get('navItem') || !currentListParams.get('navItem').get('label')) {
      // Labels options
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