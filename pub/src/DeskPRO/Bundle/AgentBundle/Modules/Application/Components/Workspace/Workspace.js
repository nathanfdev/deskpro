import React from 'react';
import { ColumnMode } from './ColumnMode';
import { SidebarMode } from './SidebarMode';

export class Workspace extends React.Component {
  render() {
    return (
      <div className="dropdown workspace-dropdown">
        <header className="dropdown-header">Your Workspace</header>

        <ColumnMode />
        <SidebarMode />

      </div>
    );
  }
}
