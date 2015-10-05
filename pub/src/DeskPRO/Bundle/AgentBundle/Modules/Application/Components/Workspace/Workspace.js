import React from 'react';
import { ColumnMode } from './ColumnMode';
import { SidebarMode } from './SidebarMode';

export class Workspace extends React.Component {
  render() {
    return (
      <div style={{width: '240px', margin: '200px', background: '#f5f6f7'}}>

        <ColumnMode />
        <SidebarMode />

      </div>);
  }
}
