import React, { PropTypes } from 'react';
import { ColumnMode } from './ColumnMode';
import { SidebarMode } from './SidebarMode';

export class Workspace extends React.Component {
  static propTypes = {
    closeFn: PropTypes.func.isRequired
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

        <ColumnMode />
        <SidebarMode />

      </div>
    );
  }
}
