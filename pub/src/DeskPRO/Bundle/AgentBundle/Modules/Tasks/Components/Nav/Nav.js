import React, { PropTypes } from 'react';
import { NavFrame, NavFrameHeaderContainer, NavFrameBody }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { GroupsContainer } from './Groups/GroupsContainer';
import { ProjectsContainer } from './Projects/ProjectsContainer';
import { AgentsContainer } from './Agents/AgentsContainer';
import { LabelsContainer } from './Labels/LabelsContainer';
import { LoadIndicator } from 'DeskPRO/Component/LoadIndicator';

export class Nav extends React.Component {

  static propTypes = {
    isDone: PropTypes.bool.isRequired
  };

  render() {
    return (
      <NavFrame>
        <NavFrameHeaderContainer icon="icon-dp-streamline-check-circle-2">Tasks</NavFrameHeaderContainer>
        <NavFrameBody>
          <div className="sidebar-list sidebar-list-filters">
            <LoadIndicator loaded={this.props.isDone}>
              <GroupsContainer />
              <ProjectsContainer />
              <AgentsContainer />
              <LabelsContainer />
            </LoadIndicator>
          </div>
        </NavFrameBody>
      </NavFrame>
    );
  }
}
