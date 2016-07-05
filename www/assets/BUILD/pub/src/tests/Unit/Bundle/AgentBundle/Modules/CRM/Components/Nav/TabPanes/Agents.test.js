// #define ~Nav DeskPRO/Bundle/AgentBundle/Modules/CRM/Components/Nav

jest.dontMock('~Nav/TabPanes/Agents');

import React from 'react';
import { toImmutable } from 'Helpers';
import { renderInCrmApp } from '../../../crm.test-helper';

describe('CRM: Agents tab  pane', () => {
  const Agents     = require('~Nav/TabPanes/Agents').Agents;
  const NestedList = require('~Nav/NestedList').NestedList;
  const fakeState  = {};
  const agents     = toImmutable({
    count:      12,
    grouped_by: 'agent_team',
    title:      'Users',
    nested:     [
      { id: 0, count: 12, title: '', type: 'agent_team' }
    ]
  });

  const render = () => {
    renderInCrmApp(fakeState, <Agents agents={agents} />);
  };

  it('should render NestedList', () => {
    spyOn(NestedList.prototype, 'render').and.callThrough();
    render();
    expect(NestedList.prototype.render).toHaveBeenCalled();
  });
});
