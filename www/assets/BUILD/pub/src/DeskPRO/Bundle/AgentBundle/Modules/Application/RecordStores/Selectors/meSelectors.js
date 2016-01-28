import { createSelector } from 'reselect';
import { createPeopleRequestSelectors } from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Selectors/peopleSelectors';
import Immutable from 'immutable';

export const meStateSelector = createPeopleRequestSelectors('me');
export const meSelector = createSelector(
  meStateSelector.recordsSel,
  users => users.first() || Immutable.fromJS({})
);

export const meStatusSelector = createSelector(
  meStateSelector.statusSel,
  users => users
);
