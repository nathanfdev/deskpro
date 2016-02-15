import { renderInRedux, fakeState, toImmutable } from 'Helpers';

/**
 * Creates fake chat app state
 *
 * @param num Number of dummy chats
 * @returns {*}
 */
export function fakeChatsState(num = 0) {
  const records = {};
  const ids = [];
  for (let id = 1; id <= num; id++) {
    records[id] = {id};
    ids.push('' + id);
  }

  return fakeState({
    Chat: {list: toImmutable({currentListParams: {}})},
    RecordsStore: {store: toImmutable({UserChat: {records, collections: {chats: ids}, statuses: {chats: {isDone: true}}}})}
  });
}

/**
 * @param num
 * @param jsx
 */
export function renderChatsInRedux(num, jsx) {
  renderInRedux(fakeChatsState(num), jsx);
}