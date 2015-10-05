import React, { PropTypes } from 'react';
import { ColumnMode } from './ColumnMode';
import { SidebarMode } from './SidebarMode';

export class Workspace extends React.Component {
  static propTypes = {
    dpWindow: PropTypes.object.isRequired,
    closeFn: PropTypes.func.isRequired
  };

  render() {
    const { dpWindow, closeFn } = this.props;

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

        <ColumnMode dpWindow={dpWindow} />
        <SidebarMode dpWindow={dpWindow} />

        <div className="dpw-top-bar-dropdown-footer">
          <a href="#" className="dpw-top-bar-dropdown-button">Save Workspace</a>
          <a href="#" className="dpw-top-bar-dropdown-button blank">Reset All</a>
        </div>

      </div>
    );
  }
}
