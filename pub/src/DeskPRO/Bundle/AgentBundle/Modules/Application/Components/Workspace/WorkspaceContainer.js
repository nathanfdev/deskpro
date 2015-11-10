import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { Workspace } from './Workspace';
import Simple from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Positioned/Simple';

@connect(state => ({
  dpWindow: state.Application.dpWindow
}))
export class WorkspaceContainer extends React.Component {

  static propTypes = {
    dpWindow: PropTypes.object.isRequired,
    dispatch: PropTypes.func.isRequired,
    positionTarget: PropTypes.node
  };

  render() {
    const { dpWindow, dispatch, positionTarget } = this.props;

    return (
      <Simple isOpen={dpWindow.get('isWorkspaceOpen')}
              positionTarget={positionTarget}
              positionAt="right bottom"
              postionMy="right top">

        <Workspace dispatch={dispatch} dpWindow={dpWindow} />
      </Simple>
    );
  }
}
