import PropTypes from 'prop-types';
import React from 'react';
import { NavFrame, NavFrameHeaderContainer, NavFrameBody }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame';
import { GroupsContainer } from './Groups/GroupsContainer';
import { ProjectsContainer } from './Projects/ProjectsContainer';
import { AgentsContainer } from './Agents/AgentsContainer';
import { LabelsContainer } from './Labels/LabelsContainer';

export class Nav extends React.Component {

  static propTypes = {
    isLoaded: PropTypes.bool.isRequired
  };

  render() {
    return (
      <NavFrame>
        <NavFrameHeaderContainer icon="icon-dp-streamline-check-circle-2">Tasks</NavFrameHeaderContainer>
        <NavFrameBody isLoaded={this.props.isLoaded}>
          <div className="sidebar-list sidebar-list-filters">
            <GroupsContainer />
            <ProjectsContainer />
            <AgentsContainer />
            <LabelsContainer />
          </div>
        </NavFrameBody>
      </NavFrame>
    );
  }
}
