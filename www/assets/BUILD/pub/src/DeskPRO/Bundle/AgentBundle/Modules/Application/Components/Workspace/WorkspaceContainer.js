import React from 'react';
import PropTypes from 'prop-types';
import { connect } from 'react-redux';
import { Simple } from 'DeskPRO/Component/Positioned/Simple';
import { Workspace } from './Workspace';
import { closeWorkspace, setColumnDimensions, setColumnMode, setSidebarMode } from '../../Actions/appActions';

@connect(state => ({
  dpWindow: state.Application.dpWindow
}))
export class WorkspaceContainer extends React.Component {

  static propTypes = {
    dpWindow:          PropTypes.object.isRequired,
    dispatch:          PropTypes.func.isRequired,
    getPositionTarget: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);
    const { dpWindow } = props;

    this.state = {
      columnMode:       dpWindow.get('columnMode'),
      sidebarMode:      dpWindow.get('sidebarMode'),
      columnDimensions: dpWindow.get('columnDimensions')
    };
  }

  setColumnMode = (mode) => {
    this.props.dispatch(setColumnMode(mode));
  };

  setColumnDimensions = (percent) => {
    this.props.dispatch(setColumnDimensions(percent));
  };

  setSidebarMode = (mode) => {
    this.props.dispatch(setSidebarMode(mode));
  };

  close = (event) => {
    event.preventDefault();

    this.resetAll(event);
    this.closeWorkspace();
  };

  closeWorkspace = () => {
    this.props.dispatch(closeWorkspace());
  };

  resetAll = (event) => {
    event.preventDefault();
    const { dispatch } = this.props;

    dispatch(setColumnMode(this.state.columnMode));
    dispatch(setColumnDimensions(this.state.columnDimensions));
    dispatch(setSidebarMode(this.state.sidebarMode));

    this.closeWorkspace();
  };

  saveWorkspace = (event) => {
    event.preventDefault();
    const { dpWindow } = this.props;

    this.setState({
      columnMode:       dpWindow.get('columnMode'),
      sidebarMode:      dpWindow.get('sidebarMode'),
      columnDimensions: dpWindow.get('columnDimensions')
    });

    this.closeWorkspace();
  };

  render() {
    const { dpWindow, getPositionTarget } = this.props;

    return (
      <Simple
        isOpen={dpWindow.get('isWorkspaceOpen')}
        positionTarget={getPositionTarget()}
        positionAt="right bottom"
        postionMy="right top"
      >

        <Workspace
          dpWindow={dpWindow}
          close={this.close}
          saveWorkspace={this.saveWorkspace}
          closeWorkspace={this.closeWorkspace}
          setColumnMode={this.setColumnMode}
          setColumnDimensions={this.setColumnDimensions}
          setSidebarMode={this.setSidebarMode}
          resetAll={this.resetAll}
        />
      </Simple>
    );
  }
}
