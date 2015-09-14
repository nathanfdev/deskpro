import React from 'react';
import { ControlBar } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';
import { OrderByContainer } from './OrderByContainer';
import { FilterContainer } from './FilterContainer';
import { ViewSwitcherContainer } from './ViewSwitcherContainer';

export class FeedbackListControlBar extends React.Component {
  render() {
    return (
      <ControlBar>
        <OrderByContainer />
        <FilterContainer />
        <ViewSwitcherContainer />
      </ControlBar>
    );
  }
}