// #define ~List DeskPRO/Bundle/AgentBundle/Modules/Feedback/Components/List

jest.dontMock('~List/ListContainer');

import React from 'react';

describe('ListContainer', () => {
  const ListContainer = require('~List/ListContainer').ListContainer;
  const List          = require('~List/List').List;

  function renderInRedux(react, state) {
    const { Provider, Connector } = require('redux/react');
    const createRedux = require('redux').createRedux;
    const redux       = createRedux(state);

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

  it('should connect to FeedbackList state', () => {
    const state = {FeedbackList: jasmine.createSpy().andReturn({})};
    renderInRedux(React, state);
    expect(state.FeedbackList).toHaveBeenCalled();
  });

  it('should render List component', () => {
    spyOn(List.prototype, 'render').andCallThrough();
    renderInRedux(React, {FeedbackList: () => ({})});
    expect(List.prototype.render).toHaveBeenCalled();
  });
});
