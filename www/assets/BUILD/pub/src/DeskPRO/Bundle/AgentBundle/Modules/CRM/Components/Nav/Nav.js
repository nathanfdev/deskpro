import React, { Component, PropTypes } from 'react';
import { NavFrame, NavFrameHeaderContainer, NavFrameBody, SectionHeader }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';
import { People } from './TabPanes/People';
import { Organizations } from './TabPanes/Organizations';
import { Agents } from './TabPanes/Agents';

export class Nav extends Component {

  static propTypes = {
    isLoaded: PropTypes.bool.isRequired,
    users: PropTypes.object.isRequired,
    organizations: PropTypes.object.isRequired,
    agents: PropTypes.object.isRequired,
    labels: PropTypes.object.isRequired
  };

  render() {
    const { labels, users, organizations, agents, isLoaded } = this.props;

    return (
      <NavFrame>
        <NavFrameHeaderContainer icon="icon-dp-streamline-connection-2">CRM</NavFrameHeaderContainer>
        <NavFrameBody isLoaded={isLoaded}>
          <SectionHeader>People</SectionHeader>
          <People users={users} labels={labels}/>

          <SectionHeader>Organizations</SectionHeader>
          <Organizations organizations={organizations} labels={labels}/>

          <SectionHeader>Agents</SectionHeader>
          <Agents agents={agents}/>
        </NavFrameBody>
      </NavFrame>
    );
  }
}
