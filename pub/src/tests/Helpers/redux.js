import Immutable from 'immutable';
import React from 'react/addons';

export function renderInRedux(state, element) {
  const { Provider } = require('react-redux');
  const createStore = require('redux').createStore;
  const store = createStore(() => state, state);

  return React.addons.TestUtils.renderIntoDocument(
    <Provider store={store}>
      {() => React.createElement(element)}
    </Provider>
  );
}

export function toImmutable(data) {
  return Immutable.fromJS(data);
}