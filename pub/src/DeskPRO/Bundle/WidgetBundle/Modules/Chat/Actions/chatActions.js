import { createAction } from 'Ampliflux';
import DpApi from 'DeskPRO/Bundle/WidgetBundle/Services/DpApi';

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
  (id, params) => new Promise(resolve => {
    DpApi
      .sendGet(`DP_API/chats/${id}/polling`, params)
      .success(response => resolve(response));
  })
);
