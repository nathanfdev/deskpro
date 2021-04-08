import { createAction } from 'DeskPRO/Component/Ampliflux';
import { loadFromApi } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

export const loadChatDepartments = createAction(
  'ADMIN_LOAD_CHAT_DEPARTMENTS',
  (reload = false) => loadFromApi('ChatDepartment', 'DP_API/chat_departments', 'all', reload)
);

export const loadTicketDepartments = createAction(
  'ADMIN_LOAD_TICKET_DEPARTMENTS',
  (reload = false) => loadFromApi('TicketDepartment', 'DP_API/ticket_departments', 'all', reload)
);

export const loadSelectableTicketDepartments = createAction(
  'ADMIN_LOAD_SELECTABLE_TICKET_DEPARTMENTS',
  (reload = false) => loadFromApi('TicketDepartment', 'DP_API/ticket_departments?selectable=1', 'selectable', reload)
);

export const loadSelectableChatDepartments = createAction(
  'ADMIN_LOAD_SELECTABLE_CHAT_DEPARTMENTS',
  (reload = false) => loadFromApi('ChatDepartment', 'DP_API/chat_departments?selectable=1', 'selectable', reload)
);
