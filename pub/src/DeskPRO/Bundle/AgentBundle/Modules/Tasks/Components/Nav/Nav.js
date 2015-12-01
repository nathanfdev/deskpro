import React, { PropTypes } from 'react';
import { NavFrame, NavFrameHeader, NavFrameBody } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { GroupsContainer } from './Groups/GroupsContainer';
import { ProjectsContainer } from './Projects/ProjectsContainer';
import { AgentsContainer } from './Agents/AgentsContainer';
import { LabelsContainer } from './Labels/LabelsContainer';
import { LoadIndicator } from 'DeskPRO/Component/LoadIndicator';

export class Nav extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    dpWindow: PropTypes.object.isRequired,
    isDone: PropTypes.bool.isRequired
  };

  render() {
    const { dispatch, dpWindow, isDone } = this.props;
    const currentApp = dpWindow.get('activeAppId');

    return (
      <NavFrame dispatch={dispatch.bind(this)} dpWindow={dpWindow}>
        <NavFrameHeader icon="icon-dp-streamline-check-circle-2" currentApp={currentApp}>
          Tasks
        </NavFrameHeader>
        <NavFrameBody>
          <div className="sidebar-list sidebar-list-filters">
            <LoadIndicator loaded={isDone}>
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
