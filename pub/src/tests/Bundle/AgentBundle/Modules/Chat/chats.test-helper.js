import { renderInRedux, fakeState, fakeRecordStoreState, toImmutable } from 'Helpers/redux';

/**
 * Creates fake chat app state
 *
 * @param num Number of dummy chats
 * @returns {*}
 */
export function fakeChatsState(num = 0) {
  const records = {};
  const ids = [];
  for (let i = 1; i <= num; i++) {
    const id = '' + i;
    records[i] = {id};
    ids.push(id);
  }

  return fakeState({
    Chat: {list: toImmutable({elements: ids, currentListParams: {}})},
    RecordStores: {Chat: {chats: fakeRecordStoreState(records, {chats: ids})}}
  });
}

/**
 * @param num
 * @param jsx
 */
export function renderChatsInRedux(num, jsx) {
  renderInRedux(fakeChatsState(num), jsx);
}