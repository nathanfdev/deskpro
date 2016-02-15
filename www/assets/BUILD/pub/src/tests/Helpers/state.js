import Immutable from 'immutable';
import $ from 'jquery';

export function toImmutable(data) {
  return Immutable.fromJS(data);
}

export function fakeState(additional = {}) {
  const base = {
    Agent: {
      settings: toImmutable({tickets: {filter_groupings: {}}})
    },
    Application: {
      routing: toImmutable({hash: {}}),
      dpWindow: toImmutable({
        activeAppId: 'whatever',
        winDims: {}
      }),
      massActions: toImmutable({selected: []})
    },
    RecordsStore: {
      store: toImmutable({})
    }
  };

  return $.extend(true, {}, base, additional);
}

export function fakeRecordsStore(store) {
  return {RecordsStore: {store: toImmutable(store)}};
}
