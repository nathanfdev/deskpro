import React, { PropTypes } from 'react';
import { ColumnMode } from './ColumnMode';
import { SidebarMode } from './SidebarMode';
import * as AppActions from '../../Actions/AppActions';

export class Workspace extends React.Component {

  static propTypes = {
    dpWindow: PropTypes.object.isRequired,
    closeFn: PropTypes.func.isRequired,
    dispatch: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);
    const { dpWindow } = props;

    this.state = {
      columnMode: dpWindow.get('columnMode'),
      sidebarMode: dpWindow.get('sidebarMode'),
      columnDimensions: dpWindow.get('columnDimensions')
    };
  }

  setColumnMode = (mode) => {
    this.props.dispatch(AppActions.setColumnMode(mode));
  };

  setColumnDimensions = (percent) => {
    this.props.dispatch(AppActions.setColumnDimensions(percent));
  };

  setSidebarMode = (mode) => {
    this.props.dispatch(AppActions.setSidebarMode(mode));
  };

  resetAll = () => {
    const { dispatch } = this.props;

    dispatch(AppActions.setColumnMode(this.state.columnMode));
    dispatch(AppActions.setColumnDimensions(this.state.columnDimensions));
    dispatch(AppActions.setSidebarMode(this.state.sidebarMode));
  };

  saveWorkspace = () => {
    const { closeFn, dpWindow } = this.props;

    this.setState({
      columnMode: dpWindow.get('columnMode'),
      sidebarMode: dpWindow.get('sidebarMode'),
      columnDimensions: dpWindow.get('columnDimensions')
    });

    closeFn();
  };

  close = () => {
    this.resetAll();
    this.props.closeFn();
  };

  render() {
    const { dpWindow } = this.props;

    return (
      <div className="dropdown workspace-dropdown">
        <header className="dropdown-header">
          <div className="header-controls">
            Your Workspace
            <span className="close">
              <a href="#"><i className="fa fa-times" onClick={this.close}></i></a>
            </span>
          </div>
        </header>

        <ColumnMode dpWindow={dpWindow}
                    onChangeMode={this.setColumnMode}
                    onChangeDimensions={this.setColumnDimensions} />
        <SidebarMode dpWindow={dpWindow}
                     onChangeMode={this.setSidebarMode} />

        <div className="dpw-top-bar-dropdown-footer">
          <a href="#" className="dpw-top-bar-dropdown-button" onClick={this.saveWorkspace}>Save Workspace</a>
          <a href="#" className="dpw-top-bar-dropdown-button blank" onClick={this.resetAll}>Reset All</a>
        </div>

      </div>
    );
  }
}
