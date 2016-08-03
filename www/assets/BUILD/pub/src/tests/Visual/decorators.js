import React from 'react';
import { IntlProvider } from 'react-intl';
import { fakeState } from 'Helpers';
import { Provider } from 'react-redux';
import { createStore } from 'redux';

export function css(jsx) {
  return (
    <div>
      <link type="text/css" rel="stylesheet" href="http://localhost:9666/pub/build/DeskPRO_AgentBundle_style.css" />
      <link type="text/css" rel="stylesheet" href="http://localhost:9666/pub/build/DeskPRO_AgentLegacyBundle_style.css" />
      {jsx}
    </div>
  );
}

export function redux(state, jsx) {
  const store = createStore(s => s, fakeState(state));

  return (
    <Provider store={store}>
      <IntlProvider locale="en">
        {jsx}
      </IntlProvider>
    </Provider>
  );
}
