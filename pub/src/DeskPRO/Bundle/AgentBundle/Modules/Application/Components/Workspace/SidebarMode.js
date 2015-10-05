import React from 'react';

export default class SidebarMode extends React.Component {
  render() {
    return (<div className="dpw-workspace-type-container">

        <div className="dpw-workspace-type-header">
          <span className="title">Sidebar Mode</span>
          <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor.</p>
        </div>

        <div className="dpw-workspace-state">

          <div className="workspace-state-a">
            <div className="workspace-state-screen">
              <span className="workspace-state-item state-sidebar active"></span>
              <span className="workspace-state-item left-column"></span>
              <span className="workspace-state-item right-column"></span>
            </div>
            <span className="workspace-state-title">Static Mode</span>
          </div>

          <div className="workspace-state-b active">
            <div className="workspace-state-screen">
              <span className="active-workspace-mark"><i className="fa fa-check"></i></span>
              <span className="workspace-state-item state-sidebar state-sidebar-hover active"><i className="fa fa-asterisk"></i></span>
              <span className="workspace-state-item left-column"></span>
              <span className="workspace-state-item right-column"></span>
            </div>
            <span className="workspace-state-title">Hover Mode</span>
          </div>
        </div>

      <div className="dpw-top-bar-dropdown-footer">
        <a href="#" className="dpw-top-bar-dropdown-button">Save Workspace</a>
        <a href="#" className="dpw-top-bar-dropdown-button blank">Reset All</a>
      </div>

    </div>);
  }
}
