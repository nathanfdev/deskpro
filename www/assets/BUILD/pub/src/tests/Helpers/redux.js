import React from 'react';
import TestUtils from 'react-dom/test-utils';
import { IntlProvider } from 'react-intl';
import { AgentApp } from 'DeskPRO/Bundle/AgentBundle/AgentApp';
import { fakeState } from './state';

export function renderInRedux(state, jsx, dispatch = null) {
  const { Provider } = require('react-redux');
  const createStore = require('redux').createStore;
  const store = createStore(s => s, fakeState(state));

  if (dispatch) {
    store.dispatch = dispatch;
  }

  return TestUtils.renderIntoDocument(
    <Provider store={store}>
      <IntlProvider locale="en">
        {jsx}
      </IntlProvider>
    </Provider>
  );
}

export function createAgentApp(state = {}) {
  return AgentApp.createStore(state);
}
