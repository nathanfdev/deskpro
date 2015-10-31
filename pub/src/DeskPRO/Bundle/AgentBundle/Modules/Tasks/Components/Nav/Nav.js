import React, { PropTypes } from 'react';
import { NavFrame, NavFrameHeader, SectionHeader }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/NavFrame/index';

export class Nav extends React.Component {

  static propTypes = {
    dispatch: PropTypes.object.isRequired,
    dpWindow: PropTypes.object.isRequired
  };

  render() {
    const { dispatch, dpWindow } = this.props;

    return (
      <NavFrame dispatch={dispatch.bind(this)} dpWindow={dpWindow}>
        <NavFrameHeader icon="icon-dp-streamline-check-circle-2">
          Tasks
        </NavFrameHeader>
      </NavFrame>
    );
  }
}
