// #define ~root DeskPRO/Bundle/AgentBundle/Modules/Tickets
// #define ~nav DeskPRO/Bundle/AgentBundle/Modules/Tickets/Components/Nav

jest.dontMock('~nav/ListGroupingModalContainer');
jest.mock('DeskPRO/Component/Positioned/Detached');

import React from 'react';
import TestUtils from 'react-dom/test-utils';
import { renderInTicketsApp } from '../../tickets.test-helper';

describe('Tickets Navigation: ListGroupingModalContainer component', () => {
  const { ListGroupingModalContainer } = require('~nav/ListGroupingModalContainer');
  const dispatch = jasmine.createSpy('dispatch');
  const attach = () => {};

  function render() {
    return renderInTicketsApp({}, <ListGroupingModalContainer filter={42} attachTo={attach} />, dispatch);
  }

  it('should render select box with grouping options', () => {
    const component       = render();
    const selectComponent = TestUtils.findRenderedDOMComponentWithTag(component, 'select');
    expect(selectComponent).not.toBeNull();
  });

  it('should dispatch an event when changing select value', () => {
    dispatch.calls.reset();
    const component       = render();
    const selectComponent = TestUtils.findRenderedDOMComponentWithTag(component, 'select');

    selectComponent.value = 'urgency';
    TestUtils.Simulate.change(selectComponent);

    expect(dispatch).toHaveBeenCalled();
  });
});
