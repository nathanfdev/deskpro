// #define ~List DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List

jest.dontMock('~List/ListContainer');

describe('ListContainer', () => {

  const React = require('react/addons');
  const ListContainer = require('~List/ListContainer').ListContainer;
  const List = require('~List/List').List;

  // TODO turn into common test helper
  // TODO rename redux -> store and createRedux -> createStore after Redux upgrade
  function renderInRedux(react, state) {
    const { Provider, Connector } = require('redux/react');
    const createRedux = require('redux').createRedux;
    const redux = createRedux(state);

    return react.addons.TestUtils.renderIntoDocument(
      <Provider redux={redux}>
        {() =>
          <Connector select={() => state}>
            {() => <ListContainer />}
          </Connector>
        }
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
