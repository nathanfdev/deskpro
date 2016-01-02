import { createSelector } from 'reselect';
import { createPeopleRequestSelectors }
  from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Selectors/peopleSelectors';
import { createFeedbackTypesRequestSelectors } from '../RecordStores/Selectors/feedbackTypesSelectors';
import { createFeedbackCommentsRequestSelectors } from '../RecordStores/Selectors/feedbackCommentsSelectors';
import { createFeedbackCategoriesRequestSelectors } from '../RecordStores/Selectors/feedbackCategoriesSelectors';
import { createFeedbackStatusCategoriesRequestSelectors } from '../RecordStores/Selectors/feedbackStatusCategoriesSelectors';
import { createFeedbackRequestSelectors } from '../RecordStores/Selectors/feedbackSelectors';

export const peopleSelector = createSelector(
  createPeopleRequestSelectors('feedback').recordsSel,
    people => people
);

export const feedbackTypesSelector = createSelector(
  createFeedbackTypesRequestSelectors('feedback').recordsSel,
    types => types
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
