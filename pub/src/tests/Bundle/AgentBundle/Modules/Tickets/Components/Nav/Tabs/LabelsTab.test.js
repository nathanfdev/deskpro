// #define ~components DeskPRO/Bundle/AgentBundle/Modules/Tickets/Components/Nav/Tabs
// #define ~common DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame

jest.dontMock('~components/LabelsTab');
jest.mock('~common/labels');

import React from 'react';
import { renderInTicketsApp } from '../../../tickets.test-helper';

describe('Tickets Navigation: LabelsTab component', () => {
  const LabelsTab        = require('~components/LabelsTab').LabelsTab;
  const LabelsDictionary = require('~common/labels').LabelsDictionary;

  function render() {
    return renderInTicketsApp({}, <LabelsTab labels={['A', 'B']} />);
  }

  it('should render LabelsTab', () => {
    spyOn(LabelsDictionary.prototype, 'render');
    render();
    expect(LabelsDictionary.prototype.render).toHaveBeenCalled();
  });
});
