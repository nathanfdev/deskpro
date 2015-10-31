import React, { PropTypes } from 'react';
import { NavFrame, NavFrameHeader } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';
import { Groups } from './Groups';
import { Projects } from './Projects';

export class Nav extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    dpWindow: PropTypes.object.isRequired
  };

  render() {
    const { dispatch, dpWindow } = this.props;

    return (
      <NavFrame dispatch={dispatch.bind(this)} dpWindow={dpWindow}>
        <NavFrameHeader icon="icon-dp-streamline-check-circle-2">
          Tasks
        </NavFrameHeader>

        <div className="sidebar-list sidebar-list-filters">
          <Groups />
          <Projects />
        </div>
      </NavFrame>
    );
  }
}
