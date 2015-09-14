jest.dontMock('DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/ListTableViewSwitcher');
jest.dontMock('DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index');
jest.dontMock('DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List/ControlBar/ViewSwitcherContainer');

describe('ViewSwitcherContainer', () => {

  const React = require('react/addons');
  const ViewSwitcherContainer =
    require('DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List/ControlBar/ViewSwitcherContainer').ViewSwitcherContainer;
  const ListTableViewSwitcher = require('DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/ListTableViewSwitcher').ListTableViewSwitcher;
  const dummyState = {viewMode: 'list', displayFields: []};

  function renderInRedux(react, state) {
    const { Provider, Connector } = require('react-redux');
    const createStore = require('redux').createStore;
    const redux = createStore(state);

    return react.addons.TestUtils.renderIntoDocument(
      <Provider redux={redux}>
        {() =>
          <Connector select={() => state}>
            {() => <ViewSwitcherContainer />}
          </Connector>
        }
      </Provider>
    );
  }

  it('should connect to ChatList state', () => {
    const state = {ChatList: jasmine.createSpy().andReturn(dummyState)};
    renderInRedux(React, state);
    expect(state.ChatList).toHaveBeenCalled();
  });

  it('should render ListTableViewSwitcher component', () => {
    spyOn(ListTableViewSwitcher.prototype, 'render').andCallThrough();
    renderInRedux(React, {ChatList: () => dummyState});
    expect(ListTableViewSwitcher.prototype.render).toHaveBeenCalled();
  });

});
