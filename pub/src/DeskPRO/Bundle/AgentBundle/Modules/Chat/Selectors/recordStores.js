import { createSelector } from 'reselect';
import { createPeopleRequestSelectors }
  from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Selectors/peopleSelectors';
import { createDepartmentsRequestSelectors }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/departmentsSelectors';
import { createChatsRequestSelectors } from '../RecordStores/Selectors/chatsSelectors';

export const chatsSelector = createSelector(
  createChatsRequestSelectors('chats').recordsSel,
    chats => chats
);

export const peopleSelector = createSelector(
  createPeopleRequestSelectors('chats').recordsSel,
    people => people
);

export const departmentsSelector = createSelector(
  createDepartmentsRequestSelectors('chats').recordsSel,
    chats => chats
);
