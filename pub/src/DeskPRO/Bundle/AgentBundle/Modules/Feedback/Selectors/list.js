import { createSelector } from 'reselect';
import { createPeopleRequestSelectors }
  from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Selectors/peopleSelectors';
import { createEmailsRequestSelectors }
  from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Selectors/emailsSelectors';
import{createFeedbackTypesRequestSelectors} from '../RecordStores/Selectors/feedbackTypesSelectors';
import{createFeedbackLabelsRequestSelectors} from '../RecordStores/Selectors/feedbackLabelsSelectors';
import{createFeedbackCommentsRequestSelectors} from '../RecordStores/Selectors/feedbackCommentsSelectors';
import{createFeedbackStatusesRequestSelectors} from '../RecordStores/Selectors/feedbackStatusesSelectors';
import{createFeedbackRequestSelectors} from '../RecordStores/Selectors/feedbackSelectors';

const stateSelector = state => state.Feedback.list;

export const sortingDataSelector = createSelector(
  stateSelector,
    list => list.get('sortOptions').toJS().find(option => option.current === true)
);

export const viewDataSelector = createSelector(
  stateSelector,
    list => list.get('viewModeOptions').toJS().find(option=> option.current === true)
);

export const filterDataSelector = createSelector(
  stateSelector,
    list => list.get('filterOptions').toJS().find(option=> option.current === true)
);

export const peopleSelector = createSelector(
  createPeopleRequestSelectors('feedback').recordsSel,
    people => people.toJS()
);

export const emailsSelector = createSelector(
  createEmailsRequestSelectors('feedback').recordsSel,
    email => email.toJS()
);

export const feedbackTypesSelector = createSelector(
  createFeedbackTypesRequestSelectors('all').recordsSel,
    types => types.toJS()
);

export const feedbackLabelsSelector = createSelector(
  createFeedbackLabelsRequestSelectors('all').recordsSel,
    labels => labels.toJS()
);

export const feedbackCommentsSelector = createSelector(
  createFeedbackCommentsRequestSelectors('feedback').recordsSel,
    comments => comments.toJS()
);

export const feedbackStatusesSelector = createSelector(
  createFeedbackStatusesRequestSelectors('feedback').recordsSel,
    statuses => statuses.toJS()
);

export const feedbackSelector = createSelector(
  createFeedbackRequestSelectors('feedback').recordsSel,
    statuses => statuses.toJS()
);
