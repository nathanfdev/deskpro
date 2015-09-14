import { createAction } from 'Ampliflux/actions';
import * as Chat from 'DeskPRO/Bundle/AgentBundle/Services/Api/Chat';

export const load = createAction(
  'CHAT_LIST_LOAD_DATA',
  (trigger, filters) => {
    return Chat.load(filters).then(promise => trigger(promise.getData().data))
  }
);

export const toggleSort = createAction(
  'CHAT_LIST_SORT',
  (trigger, sort) => trigger(sort)
);

