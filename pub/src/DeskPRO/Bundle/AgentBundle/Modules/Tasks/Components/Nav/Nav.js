import React, { PropTypes } from 'react';
import { NavFrame, NavFrameHeader, NavFrameBody, TabSpinner } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { GroupsContainer } from './Groups/GroupsContainer';
import { ProjectsContainer } from './Projects/ProjectsContainer';
import { AgentsContainer } from './Agents/AgentsContainer';
import { LabelsContainer } from './Labels/LabelsContainer';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';

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
            <TabSpinner loaded={isDone} color={constants.APP_COLOURS[currentApp]}>
              <GroupsContainer />
              <ProjectsContainer />
              <AgentsContainer />
              <LabelsContainer />
            </TabSpinner>
          </div>
        </NavFrameBody>
      </NavFrame>
    );
  }
}
