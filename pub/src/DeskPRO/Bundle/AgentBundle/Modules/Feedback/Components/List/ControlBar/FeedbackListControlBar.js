import React from 'react';
import { ControlBar, ControlButtonsRow } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/ControlBar';
import { OrderByContainer } from './OrderByContainer';
import { FilterContainer } from './FilterContainer';
import { ViewSwitcherContainer } from './ViewSwitcherContainer';
import { OrderByDropdownContainer } from './OrderByDropdownContainer';
import { ViewSwitcherDropdownContainer } from './ViewSwitcherDropdownContainer';

export class FeedbackListControlBar extends React.Component {
  render() {
    return (
      <ControlBar>
        <ControlButtonsRow>
          <OrderByContainer />
          <li>
            <hr/>
          </li>
          <FilterContainer />
          <li>
            <hr/>
          </li>
          <ViewSwitcherContainer />
        </ControlButtonsRow>
        <OrderByDropdownContainer />
        <ViewSwitcherDropdownContainer />
      </ControlBar>
    );
  }
}