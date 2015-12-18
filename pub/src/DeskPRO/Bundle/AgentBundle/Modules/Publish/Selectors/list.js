import { createSelector } from 'reselect';
import { createPeopleRequestSelectors }
  from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Selectors/peopleSelectors';

const stateSelector = state => state.Publish.list;

export const currentListParamsSelector = createSelector(
  stateSelector,
    state => state.get('currentListParams')
);


export const peopleSelector = createSelector(
  createPeopleRequestSelectors('publish').recordsSel,
    people => people
);
