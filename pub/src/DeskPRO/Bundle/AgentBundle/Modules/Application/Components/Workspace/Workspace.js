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
    this.setState({
      columnMode: mode
    });
  };

  setColumnDimensions = (percent) => {
    this.setState({
      columnDimensions: percent
    });
  };

  setSidebarMode = (mode) => {
    this.setState({
      sidebarMode: mode
    });
  };

  resetAll = () => {
    const { dpWindow } = this.props;

    this.setState({
      columnMode: dpWindow.get('columnMode'),
      sidebarMode: dpWindow.get('sidebarMode'),
      columnDimensions: dpWindow.get('columnDimensions')
    });
  };

  saveWorkspace = () => {
    this.props.dispatch(AppActions.updateWorkspace(this.state));
  };

  render() {
    const { closeFn } = this.props;

    return (
      <div className="dropdown workspace-dropdown">
        <header className="dropdown-header">
          <div className="header-controls">
            Your Workspace
            <span className="close">
              <a href="#"><i className="fa fa-times" onClick={closeFn}></i></a>
            </span>
          </div>
        </header>

        <ColumnMode state={this.state}
                    onChangeMode={this.setColumnMode}
                    onChangeDimensions={this.setColumnDimensions} />
        <SidebarMode state={this.state}
                     onChangeMode={this.setSidebarMode} />

        <div className="dpw-top-bar-dropdown-footer">
          <a href="#" className="dpw-top-bar-dropdown-button" onClick={this.saveWorkspace}>Save Workspace</a>
          <a href="#" className="dpw-top-bar-dropdown-button blank" onClick={this.resetAll}>Reset All</a>
        </div>

      </div>
    );
  }
}
