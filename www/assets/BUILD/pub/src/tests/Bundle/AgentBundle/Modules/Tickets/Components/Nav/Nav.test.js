// #define ~root DeskPRO/Bundle/AgentBundle/Modules/Tickets
// #define ~nav DeskPRO/Bundle/AgentBundle/Modules/Tickets/Components/Nav

jest.dontMock('~nav/Nav');

import React from 'react';
import ReactDOM from 'react-dom';
import { renderInTicketsApp } from '../../tickets.test-helper';

describe('Tickets Navigation: Nav component', () => {
  const Nav                 = require('~nav/Nav').Nav;
  const FiltersTabContainer = require('~nav/Tabs/FiltersTab/FiltersTabContainer').FiltersTabContainer;
  const LabelsTabContainer  = require('~nav/Tabs/LabelsTabContainer').LabelsTabContainer;
  const StarsTabContainer   = require('~nav/Tabs/StarsTabContainer').StarsTabContainer;
  const LoadIndicator       = require('DeskPRO/Component/LoadIndicator').LoadIndicator;
  const Tab                 = require('DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/tabs').Tab;

  let node;

  function render(isLoaded = true) {
    node = ReactDOM.findDOMNode(renderInTicketsApp({}, <Nav isLoaded={isLoaded} />));
  }

  it("should render a spinner while data aren't loaded", () => {
    spyOn(LoadIndicator.prototype, 'render').and.callThrough();
    render(false);
    expect(LoadIndicator.prototype.render).toHaveBeenCalled();
  });

  it("shouldn't render tabs while data aren't loaded", () => {
    spyOn(Tab.prototype, 'render');
    render(false);
    expect(Tab.prototype.render).not.toHaveBeenCalled();
  });

  it('should render "Tickets" header', () => {
    render();

    const h1 = node.querySelector('h1');
    expect(h1.textContent).toEqual('Tickets');
  });

  it('should render first Filters tab', () => {
    spyOn(FiltersTabContainer.prototype, 'render');

    render();

    const links = node.querySelectorAll('a');
    expect(links[0].textContent).toEqual('Filters');
    expect(FiltersTabContainer.prototype.render).toHaveBeenCalled();
  });

  it('should render second Labels tab', () => {
    spyOn(LabelsTabContainer.prototype, 'render');

    render();

    const links = node.querySelectorAll('a');
    expect(links[1].textContent).toEqual('Labels');
    expect(LabelsTabContainer.prototype.render).toHaveBeenCalled();
  });

  it('should render third Stars tab', () => {
    spyOn(StarsTabContainer.prototype, 'render');

    render();

    const links = node.querySelectorAll('a');
    expect(links[2].textContent).toEqual('Stars');
    expect(StarsTabContainer.prototype.render).toHaveBeenCalled();
  });
});
