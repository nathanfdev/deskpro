// #define ~components DeskPRO/Bundle/AgentBundle/Modules/CRM/Components

jest.dontMock('~components/CrmApp');

import React from 'react';
import { renderInRedux, fakeState } from 'Helpers';

describe('CRM: CrmApp component', () => {
  const CrmApp = require('~components/CrmApp').CrmApp;
  const NavContainer = require('~components/Nav/NavContainer').NavContainer;
  const ListContainer = require('~components/List/ListContainer').ListContainer;

  function render() {
    return renderInRedux(fakeState({}), <CrmApp/>);
  }

  it('should render NavContainer', () => {
    spyOn(NavContainer.prototype, 'render');
    render();
    expect(NavContainer.prototype.render).toHaveBeenCalled();
  });

  it('should render ListContainer', () => {
    spyOn(ListContainer.prototype, 'render');
    render();
    expect(ListContainer.prototype.render).toHaveBeenCalled();
  });
});
