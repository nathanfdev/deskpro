// #define ~root DeskPRO/Bundle/AgentBundle/Modules/Tickets
// #define ~nav DeskPRO/Bundle/AgentBundle/Modules/Tickets/Components/Nav

jest.dontMock('~nav/FilterEditPopupContainer');
jest.mock('DeskPRO/Component/Positioned/Detached');

import React from 'react';
import TestUtils from 'react-addons-test-utils';
import { renderInTicketsApp } from '../../tickets.test-helper';

describe('Tickets Navigation: FilterEditPopupContainer component', () => {
  const { FilterEditPopupContainer } = require('~nav/FilterEditPopupContainer');
  const actions = require('~root/Actions/navActions');
  const dispatch = jasmine.createSpy('dispatch');

  function render() {
    return renderInTicketsApp({}, <FilterEditPopupContainer filterId={42} />, dispatch);
  }

  it('should render select box with grouping options', () => {
    const component = render();
    const selectComponent = TestUtils.findRenderedDOMComponentWithTag(component, 'select');
    expect(selectComponent).not.toBeNull();
  });

  it('should dispatch the applyFilterEditing() event when changing select value', () => {
    dispatch.calls.reset();
    spyOn(actions, 'applyFilterEditing').and.callThrough();
    const component = render();
    const selectComponent = TestUtils.findRenderedDOMComponentWithTag(component, 'select');

    selectComponent.value = 'urgency';
    TestUtils.Simulate.change(selectComponent);

    expect(actions.applyFilterEditing).toHaveBeenCalled();
  });
});
