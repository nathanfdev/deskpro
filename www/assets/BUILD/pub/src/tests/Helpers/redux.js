import React from 'react';
import TestUtils from 'react-addons-test-utils';

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
