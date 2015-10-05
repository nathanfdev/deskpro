import React from 'react';
import { ColumnMode } from './ColumnMode';
import { SidebarMode } from './SidebarMode';

export class Workspace extends React.Component {
  render() {
    return (
      <div className="dropdown workspace-dropdown">
        <header className="dropdown-header">
          <div className="header-controls">
            Your Workspace
            <span className="close">
              <a href="#"><i className="fa fa-times"></i></a>
            </span>
          </div>
        </header>

        <ColumnMode />
        <SidebarMode />

      </div>
    );
  }
}
