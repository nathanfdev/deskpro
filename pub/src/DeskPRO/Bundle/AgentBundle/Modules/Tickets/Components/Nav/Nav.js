import React, { Component } from 'react';
import { NavFrame, NavFrameHeader, NavFrameBody, TabsPaneStatefulContainer, Tab }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { FiltersTabContainer, LabelsTabContainer, StarsTabContainer } from './Tabs/index';
import { FilterEditPopupContainer } from './FilterEditPopupContainer';

export class Nav extends Component {
  render() {
    return (
      <NavFrame>
        <div part="outer">
          <FilterEditPopupContainer />
        </div>
        <div part="inner">
          <NavFrameHeader icon="icon icon-dp-streamline-mail-2">Tickets</NavFrameHeader>
          <NavFrameBody>
            <TabsPaneStatefulContainer id="tab">
              <Tab title="Filters">
                <FiltersTabContainer />
              </Tab>
              <Tab title="Labels">
                <LabelsTabContainer />
              </Tab>
              <Tab title="Stars">
                <StarsTabContainer />
              </Tab>
            </TabsPaneStatefulContainer>
          </NavFrameBody>
        </div>
      </NavFrame>
    );
  }
}
