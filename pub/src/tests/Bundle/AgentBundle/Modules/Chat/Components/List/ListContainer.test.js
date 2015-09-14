// #define ~List DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List

jest.dontMock('~List/ListContainer');

describe('ListContainer', () => {
  const React = require('react/addons');
  const ListContainer = require('~List/ListContainer').ListContainer;
  const List = require('~List/List').List;

  // TODO turn into common test helper
  function renderInRedux(react, state) {
    const { Provider } = require('react-redux');
    const createStore = require('redux').createStore;
    const store = createStore(() => state, state);

    return react.addons.TestUtils.renderIntoDocument(
      <Provider store={store}>
        {() => <ListContainer />}
      </Provider>
    );
  }

  it('should connect to ChatList state', () => {
    const state = {ChatList: jasmine.createSpy().andReturn({})};
    renderInRedux(React, state);
    expect(state.ChatList).toHaveBeenCalled();
  });

  it('should render List component', () => {
    spyOn(List.prototype, 'render').andCallThrough();
    renderInRedux(React, {ChatList: () => ({})});
    expect(List.prototype.render).toHaveBeenCalled();
  });
});
