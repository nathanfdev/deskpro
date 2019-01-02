import { createAction } from 'DeskPRO/Component/Ampliflux';
import { loadFromApi } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

export const loadSelectableTicketDepartments = createAction(
  'ADMIN_LOAD_SELECTABLE_TICKET_DEPARTMENTS',
  () => loadFromApi('TicketDepartment', 'DP_API/ticket_departments?selectable=1', 'selectable')
);

export const loadSelectableChatDepartments = createAction(
  'ADMIN_LOAD_SELECTABLE_CHAT_DEPARTMENTS',
  () => loadFromApi('ChatDepartment', 'DP_API/chat_departments?selectable=1', 'selectable')
);
