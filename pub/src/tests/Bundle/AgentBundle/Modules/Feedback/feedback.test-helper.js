import { renderInRedux, fakeState, fakeRecordStoreState, toImmutable } from 'Helpers/redux';

/**
 * Creates fake chat app state
 *
 * @param num Number of dummy chats
 * @returns {*}
 */
export function fakeFeedbackState(num = 0) {
  const records = {};
  const ids = [];
  for (let i = 1; i <= num; i++) {
    const id = '' + i;
    records[i] = { id };
    ids.push(id);
  }

  return fakeState({
    Feedback: { list: toImmutable({ elements: ids, currentListParams: {} }) },
    RecordStores: { Feedback: { feedback: fakeRecordStoreState(records, { feedback: ids }) } }
  });
}

/**
 * @param num
 * @param jsx
 */
export function renderFeedbackInRedux(num, jsx) {
  renderInRedux(fakeFeedbackState(num), jsx);
}