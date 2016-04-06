// #define ~nav DeskPRO/Bundle/AgentBundle/Modules/CRM/Components/Nav

jest.dontMock('~nav/Nav');

import React from 'react';
import ReactDOM from 'react-dom';
import { renderInCrmApp } from '../../crm.test-helper';

describe('CRM Navigation: Nav component', () => {
  const Nav = require('~nav/Nav').Nav;
  const LoadIndicator = require('DeskPRO/Component/LoadIndicator').LoadIndicator;
  const People = require('~nav/TabPanes/People').People;
  const Organizations = require('~nav/TabPanes/Organizations').Organizations;
  const Agents = require('~nav/TabPanes/Agents').Agents;

  let component;
  let node;

  function render(isLoaded = true) {
    component = renderInCrmApp({}, <Nav isLoaded={isLoaded}/>);
    node = ReactDOM.findDOMNode(component);
  }

  it("should render a spinner while data aren't loaded", () => {
    spyOn(LoadIndicator.prototype, 'render').and.callThrough();
    render(false);
    expect(LoadIndicator.prototype.render).toHaveBeenCalled();
  });

  it("shouldn't render People while data aren't loaded", () => {
    spyOn(People.prototype, 'render');
    render(false);
    expect(People.prototype.render).not.toHaveBeenCalled();
  });

  it('should render "CRM" header', () => {
    render();

    const h1 = node.querySelector('h1');
    expect(h1.textContent).toEqual('CRM');
  });

  it('should render People', () => {
    spyOn(People.prototype, 'render');

    render();

    expect(People.prototype.render).toHaveBeenCalled();
  });

  it('should render Organizations', () => {
    spyOn(Organizations.prototype, 'render');

    render();

    expect(Organizations.prototype.render).toHaveBeenCalled();
  });

  it('should render Organizations', () => {
    spyOn(Agents.prototype, 'render');

    render();

    expect(Agents.prototype.render).toHaveBeenCalled();
  });
});
