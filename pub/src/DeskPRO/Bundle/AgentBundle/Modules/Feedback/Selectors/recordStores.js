import { createSelector } from 'reselect';
import { createPeopleRequestSelectors }
  from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Selectors/peopleSelectors';

export const peopleSelector = createSelector(
  createPeopleRequestSelectors('feedback').recordsSel,
    people => people
);
