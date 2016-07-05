import React from 'react';
import { IntlProvider } from 'react-intl';
import { fakeState } from 'Helpers';

export function css(jsx) {
  return (
    <div>
      <link type="text/css" rel="stylesheet" href="http://localhost:9666/pub/build/DeskPRO_AgentBundle_style.css" />
      {jsx}
    </div>
  );
}

export function redux(state, jsx) {
  const { Provider } = require('react-redux');
  const createStore = require('redux').createStore;
  const store = createStore(s => s, fakeState(state));

  return (
    <Provider store={store}>
      <IntlProvider locale="en">
        {jsx}
      </IntlProvider>
    </Provider>
  );
}