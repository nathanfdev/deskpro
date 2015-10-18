import { createSelector } from 'reselect';
import { createPeopleRequestSelectors }
  from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Selectors/peopleSelectors';

export const meStateSelector = createPeopleRequestSelectors('me');
export const meSelector = createSelector(
  meStateSelector.recordsSel,
  users => users.first()
);

export const meStatusSelector = createSelector(
  meStateSelector.statusSel,
  users => users
);
