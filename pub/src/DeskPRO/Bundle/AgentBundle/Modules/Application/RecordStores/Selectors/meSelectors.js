import { createSelector } from 'reselect';
import { createPeopleRequestSelectors }
  from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Selectors/peopleSelectors';

export const meSelector = createPeopleRequestSelectors('me');
export const meObjSelector = createSelector(
  meSelector.recordsSel,
    users => users
);