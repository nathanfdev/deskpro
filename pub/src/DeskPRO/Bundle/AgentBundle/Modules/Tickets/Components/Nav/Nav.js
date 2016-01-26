import React, { Component, PropTypes } from 'react';
import { NavFrame, NavFrameHeaderContainer, NavFrameBody, TabsPaneStatefulContainer, Tab }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { FiltersTabContainer, LabelsTabContainer, StarsTabContainer } from './Tabs/index';

export class Nav extends Component {
  render() {
    return (
      <NavFrame>
        <NavFrameHeaderContainer icon="icon icon-dp-streamline-mail-2">Tickets</NavFrameHeaderContainer>
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
      </NavFrame>
    );
  }
}
