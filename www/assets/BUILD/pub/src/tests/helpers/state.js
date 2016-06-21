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
    if (state[i] === null || typeof state[i] !== 'object') {
      throw `State error: ${state[i]} is not an object`;
    }

    Object.keys(state[i]).forEach(j => {
      state[i][j] = toImmutable(state[i][j]);
    });
  });

  return state;
}

export function fakeRecordsStore(store) {
  return {RecordsStore: {store: toImmutable(store)}};
}
