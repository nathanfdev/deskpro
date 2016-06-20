// #define ~commonNav DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame
// #define ~Nav DeskPRO/Bundle/AgentBundle/Modules/CRM/Components/Nav

jest.dontMock('~Nav/TabPanes/Organizations');

import React from 'react';
import { toImmutable } from 'helpers';
import { renderInCrmApp } from '../../../crm.test-helper';

describe('CRM: Organizations tab  pane', () => {
  const Organizations             = require('~Nav/TabPanes/Organizations').Organizations;
  const ListItemContainer         = require('~Nav/ListItemContainer').ListItemContainer;
  const TabsPaneStatefulContainer = require('~commonNav/tabs').TabsPaneStatefulContainer;
  const fakeState                 = {};
  const organizations             = toImmutable({
    count:  79,
    nested: []
  });

  const render = () => {
    renderInCrmApp(fakeState, <Organizations organizations={organizations} />);
  };

  it('should render TabsPaneStatefulContainer', () => {
    spyOn(TabsPaneStatefulContainer.prototype, 'render').and.callThrough();
    render();
    expect(TabsPaneStatefulContainer.prototype.render).toHaveBeenCalled();
  });
  it('should render ListItemContainer', () => {
    spyOn(ListItemContainer.prototype, 'render').and.callThrough();
    render();
    expect(ListItemContainer.prototype.render).toHaveBeenCalled();
  });
});
