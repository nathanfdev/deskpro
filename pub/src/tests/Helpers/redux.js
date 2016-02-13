import Immutable from 'immutable';
import React from 'react';
import TestUtils from 'react-addons-test-utils';
import { merge } from 'lodash'

export function renderInRedux(state, element) {
  const { Provider } = require('react-redux');
  const createStore = require('redux').createStore;
  const store = createStore(() => state, state);

  return TestUtils.renderIntoDocument(
    <Provider store={store}>
      {element}
    </Provider>
  );
}

export function toImmutable(data) {
  return Immutable.fromJS(data);
}

export function fakeState(additional = {}) {
  const frs = fakeRecordStoreState;

  const base = {
    Application: {
      routing: toImmutable({hash: {}}),
      dpWindow: toImmutable({activeAppId: 'whatever'}),
      massActions: toImmutable({selected: []})
    },
    RecordStores: {
      CRM: {people: frs()},
      Agent: {departments: frs()}
    }
  };

  return merge(base, additional);
}

export function fakeRecordStoreState(records = {}, requests = {}) {
  return toImmutable({
    records,
    requests,
    status: ''
  });
}