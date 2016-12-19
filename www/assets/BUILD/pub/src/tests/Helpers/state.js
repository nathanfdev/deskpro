import Immutable from 'immutable';
import $ from 'jquery';

export function toImmutable(data) {
  return Immutable.fromJS(data);
}

export function fakeState(additional = {}) {
  const base = {
    Agent: {
      settings: {tickets: {filter_groupings: {}}}
    },
    Application: {
      routing: {hash: {}},
      dpWindow: {
        activeAppId: 'whatever',
        winDims: {}
      },
      massActions: {selected: []}
    },
    RecordsStore: {
      store: {}
    }
  };
  const state = $.extend(true, {}, base, additional);

  Object.keys(state).forEach(i => {
    if (state[i] !== null && typeof state[i] === 'object') {
      Object.keys(state[i]).forEach(j => {
        state[i][j] = toImmutable(state[i][j]);
      });
    }
  });

  return state;
}

export function fakeRecordsStore(store) {
  return {RecordsStore: {store}};
}

export function fakeRecordsStoreRequest(recordName, requestName, records, status = {isDone: true}) {
  const recordsMap = {};
  records.forEach(record => recordsMap[record.id] = record);

  return {
    RecordsStore: {
      store: {
        [recordName]: {
          records: recordsMap,

          collections: { [requestName]: Object.keys(recordsMap) },
          statuses:    { [requestName]: status }
        }
      }
    }
  };
}
