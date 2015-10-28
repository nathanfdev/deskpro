import { createSelector } from 'reselect';
import { createPeopleRequestSelectors }
  from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Selectors/peopleSelectors';
import { createEmailsRequestSelectors }
  from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Selectors/emailsSelectors';
import { createFeedbackTypesRequestSelectors } from '../RecordStores/Selectors/feedbackTypesSelectors';
import { createFeedbackLabelsRequestSelectors } from '../RecordStores/Selectors/feedbackLabelsSelectors';
import { createFeedbackCommentsRequestSelectors } from '../RecordStores/Selectors/feedbackCommentsSelectors';
import { createFeedbackStatusesRequestSelectors } from '../RecordStores/Selectors/feedbackStatusesSelectors';
import { createFeedbackCategoriesRequestSelectors } from '../RecordStores/Selectors/feedbackCategoriesSelectors';
import { createFeedbackRequestSelectors } from '../RecordStores/Selectors/feedbackSelectors';
import { hashStateSelectorFactory } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Selectors/routing';

const stateSelector = state => state.Feedback.list;

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
    list => list.get('isComments')
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
  createFeedbackLabelsRequestSelectors('all').recordsSel,
    labels => labels
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
