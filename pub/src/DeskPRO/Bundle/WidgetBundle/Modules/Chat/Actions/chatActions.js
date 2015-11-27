import { createAction } from 'Ampliflux';
import DpApi from 'DeskPRO/Bundle/WidgetBundle/Services/DpApi';

export const createChat = createAction(
  'WIDGET_CHAT_CREATE_NEW',
  params => new Promise(resolve => {
    DpApi
      .sendPost('DP_API/chat/create', params)
      .success(response => resolve(response));
  })
);
