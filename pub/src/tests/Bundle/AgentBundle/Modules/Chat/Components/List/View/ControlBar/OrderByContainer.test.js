jest.dontMock('DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/OrderBy');
jest.dontMock('DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index');
jest.dontMock('DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List/ControlBar/OrderByContainer');

describe('OrderByContainer', () => {

  const React = require('react/addons');
  const OrderByContainer =
    require('DeskPRO/Bundle/AgentBundle/Modules/Chat/Components/List/ControlBar/OrderByContainer').OrderByContainer;
  const OrderBy = require('DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/OrderBy').OrderBy;
  const dummyState = {sort: 'id', order: 'desc', sortOptions: [], sortName: 'sort'};

  function renderInRedux(react, state) {
    const { Provider, Connector } = require('react-redux');
    const createRedux = require('redux').createRedux;
    const redux = createRedux(state);

    return react.addons.TestUtils.renderIntoDocument(
      <Provider redux={redux}>
        {() =>
          <Connector select={() => state}>
            {() => <OrderByContainer />}
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

  it('should render OrderBy component', () => {
    spyOn(OrderBy.prototype, 'render').andCallThrough();
    renderInRedux(React, {ChatList: () => dummyState});
    expect(OrderBy.prototype.render).toHaveBeenCalled();
  });

});
