import React, { PropTypes } from 'react';
import { NavFrame, NavFrameHeader } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { Groups } from './Groups';
import { Projects } from './Projects/Projects';
import { Agents } from './Agents';
import { Labels } from './Labels';
import * as TasksActions from '../../Actions/tasksActions';

export class Nav extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    dpWindow: PropTypes.object.isRequired,
    projects: PropTypes.object.isRequired,
    agents: PropTypes.object.isRequired,
    labels: PropTypes.object.isRequired,
    groups: PropTypes.object.isRequired
  };

  onApplyListParams = filter => {
    this.props.dispatch(TasksActions.applyListParams(filter));
  };

  render() {
    const { dispatch, dpWindow, projects, agents, labels, groups } = this.props;

    return (
      <NavFrame dispatch={dispatch.bind(this)} dpWindow={dpWindow}>
        <NavFrameHeader icon="icon-dp-streamline-check-circle-2">
          Tasks
        </NavFrameHeader>

        <div className="sidebar-list sidebar-list-filters">
          <Groups groups={groups}
                  dispatch={dispatch} />
          <Projects projects={projects}
                    onApplyListParams={this.onApplyListParams} />
          <Agents agents={agents}
                  onApplyListParams={this.onApplyListParams} />
          <Labels labels={labels}
                  dispatch={dispatch} />
        </div>
      </NavFrame>
    );
  }
}
