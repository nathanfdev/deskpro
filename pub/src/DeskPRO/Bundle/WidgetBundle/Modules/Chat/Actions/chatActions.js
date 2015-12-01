import { createAction } from 'Ampliflux';
import DpApi from 'DeskPRO/Bundle/WidgetBundle/Services/DpApi';
import { compileParams } from 'DeskPRO/Bundle/AgentBundle/Services/ApiHelpers';

export const createChat = createAction(
  'WIDGET_CHAT_CREATE_NEW',
  params => new Promise(resolve => {
    DpApi
      .sendPost('DP_API/chats/create', params)
      .success(response => resolve(response));
  })
);

export const pollingChat = createAction(
  'WIDGET_CHAT_POLLING',
  (chatId, params) => new Promise(resolve => {
    DpApi
      .sendGet(`DP_API/chats/${chatId}/polling?` + compileParams(params))
      .success(response => resolve(response));
  })
);

export const sendChatMessage = createAction(
  'WIDGET_CHAT_SEND_MESSAGE',
  (chatId, params) => new Promise(resolve => {
    DpApi
      .sendPost(`DP_API/chats/${chatId}/messages`, params)
      .success(response => resolve(response));
  })
);

export const endChat = createAction(
  'WIDGET_CHAT_END',
  chatId => DpApi.sendPost(`DP_API/chats/${chatId}/end`)
);

export const reopenChat = createAction(
  'WIDGET_CHAT_REOPEN',
  chatId => DpApi.sendPost(`DP_API/chats/${chatId}/reopen`)
);

export const sendFeedback = createAction('WIDGET_CHAT_SEND_FEEDBACK');
