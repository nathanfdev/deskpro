import { renderInRedux } from 'helpers';
import { initialState } from 'DeskPRO/Bundle/AgentBundle/Modules/Chat/Reducers/list';

/**
 * Creates fake chat app state
 *
 * @param num Number of dummy chats
 * @returns {*}
 */
export function fakeChatsState(num = 0) {
  const records = {};
  const ids     = [];
  for (let id = 1; id <= num; id++) {
    records[id] = { id };
    ids.push(`${id}`);
  }

  return {
    Chat:         { list: initialState },
    RecordsStore: {
      store: {
        UserChat: {
          records,

          collections: { chats: ids },
          statuses:    { chats: { isDone: true } }
        }
      }
    }
  };
}

/**
 * @param num
 * @param jsx
 */
export function renderChatsInRedux(num, jsx) {
  renderInRedux(fakeChatsState(num), jsx);
}
