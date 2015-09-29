import { createSelector } from 'reselect';
import { createPeopleRequestSelectors }
  from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Selectors/peopleSelectors';
import{createFeedbackTypesRequestSelectors} from '../RecordStores/Selectors/feedbackTypesSelectors';
import{createFeedbackLabelsRequestSelectors} from '../RecordStores/Selectors/feedbackLabelsSelectors';

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

export const feedbackTypesSelector = createSelector(
  createFeedbackTypesRequestSelectors('all').recordsSel,
    types => types.toJS()
);

export const feedbackLabelsSelector = createSelector(
  createFeedbackLabelsRequestSelectors('all').recordsSel,
    labels => labels.toJS()
);