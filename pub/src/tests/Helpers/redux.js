import Immutable from 'immutable';
import React from 'react';
import TestUtils from 'react-addons-test-utils';

export function renderInRedux(state, element) {
  const { Provider } = require('react-redux');
  const createStore = require('redux').createStore;
  const store = createStore(() => state, state);

  return TestUtils.renderIntoDocument(
    <Provider store={store}>
      {React.createElement(element)}
    </Provider>
  );
}

export function toImmutable(data) {
  return Immutable.fromJS(data);
}