import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { NavFrame, NavFrameHeaderContainer, NavFrameBody, SectionHeader }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';
import { People } from './TabPanes/People';
import { Organizations } from './TabPanes/Organizations';
import { Agents } from './TabPanes/Agents';

export class Nav extends Component {

  static propTypes = {
    isLoaded:           PropTypes.bool.isRequired,
    users:              PropTypes.object.isRequired,
    organizations:      PropTypes.object.isRequired,
    agents:             PropTypes.object.isRequired,
    organizationLabels: PropTypes.object.isRequired,
    personLabels:       PropTypes.object.isRequired
  };

  render() {
    const { organizationLabels, personLabels, users, organizations, agents, isLoaded } = this.props;

    return (
      <NavFrame>
        <NavFrameHeaderContainer icon="icon-dp-streamline-connection-2">CRM</NavFrameHeaderContainer>
        <NavFrameBody isLoaded={isLoaded}>
          <SectionHeader>People</SectionHeader>
          <People users={users} labels={personLabels} />

          <SectionHeader>Organizations</SectionHeader>
          <Organizations organizations={organizations} labels={organizationLabels} />

          <SectionHeader>Agents</SectionHeader>
          <Agents agents={agents} />
        </NavFrameBody>
      </NavFrame>
    );
  }
}
