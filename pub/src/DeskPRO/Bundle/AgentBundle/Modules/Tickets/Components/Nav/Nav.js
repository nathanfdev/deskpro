import React, { Component, PropTypes } from 'react';
import { NavFrame, NavFrameHeader, NavFrameBody, TabsPaneStatefulContainer, Tab }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { FiltersTabContainer, LabelsTabContainer, StarsTabContainer } from './Tabs/index';

export class Nav extends Component {
  static propTypes = {
    currentApp: PropTypes.string.isRequired
  };

  render() {
    const { currentApp } = this.props;

    return (
      <NavFrame>
        <NavFrameHeader icon="icon icon-dp-streamline-mail-2" currentApp={currentApp}>Tickets</NavFrameHeader>
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
