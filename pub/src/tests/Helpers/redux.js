const React = require('react/addons');

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
