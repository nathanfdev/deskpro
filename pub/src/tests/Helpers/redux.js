import Immutable from 'immutable';
import React from 'react';
import TestUtils from 'react-addons-test-utils';
import $ from 'jquery';

export function renderInRedux(state, jsx, dispatch = null) {
  const { Provider } = require('react-redux');
  const createStore = require('redux').createStore;
  const store = createStore(() => state, state);

  if (dispatch) {
    store.dispatch = dispatch;
  }

  return TestUtils.renderIntoDocument(
    <Provider store={store}>
      {jsx}
    </Provider>
  );
}

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
    RecordStores: {
      CRM: {people: fakeRecordStoreState()},
      Agent: {departments: fakeRecordStoreState()}
    }
  };

  return $.extend(true, {}, base, additional);
}

export function fakeRecordStoreState(records = {}, requests = {}) {
  return toImmutable({
    records,
    requests,
    status: ''
  });
}

export function getState() {
  return reduxStore.getState();
}