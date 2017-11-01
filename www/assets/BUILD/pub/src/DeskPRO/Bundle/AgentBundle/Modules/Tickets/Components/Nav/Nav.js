import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { NavFrame, NavFrameHeaderContainer, NavFrameBody, TabsPaneStatefulContainer, Tab }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';
import { FiltersTabContainer, LabelsTabContainer, StarsTabContainer } from './Tabs';

export class Nav extends Component {
  static propTypes = {
    isLoaded: PropTypes.bool.isRequired
  };

  render() {
    return (
      <NavFrame>
        <NavFrameHeaderContainer icon="icon icon-dp-streamline-mail-2">Tickets</NavFrameHeaderContainer>
        <NavFrameBody isLoaded={this.props.isLoaded}>
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
