import { createAction } from 'DeskPRO/Component/Ampliflux';
import { loadFromApi } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

export const loadChatCustomFieldsAction = createAction(
  'ADMIN_LOAD_CHAT_CUSTOM_FIELDS',
  () => loadFromApi('ChatCustomField', 'DP_API/user_chat_custom_fields', 'all')
);
