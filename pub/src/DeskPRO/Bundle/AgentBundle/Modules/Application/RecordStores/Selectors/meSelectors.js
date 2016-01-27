import { createSelector } from 'reselect';
import { createPeopleRequestSelectors } from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Selectors/peopleSelectors';

const meStateSelector = createPeopleRequestSelectors('me');
export const meSelector = createSelector(meStateSelector.recordsSel, users => users.first());
