import PropTypes from 'prop-types';
import React from 'react';
import { ColumnMode } from './ColumnMode';
import { SidebarMode } from './SidebarMode';

export class Workspace extends React.Component {

  static propTypes = {
    dpWindow:            PropTypes.object.isRequired,
    close:               PropTypes.func.isRequired,
    closeWorkspace:      PropTypes.func.isRequired,
    resetAll:            PropTypes.func.isRequired,
    setColumnDimensions: PropTypes.func.isRequired,
    saveWorkspace:       PropTypes.func.isRequired,
    setColumnMode:       PropTypes.func.isRequired,
    setSidebarMode:      PropTypes.func.isRequired
  };

  render() {
    const { close, dpWindow, resetAll, saveWorkspace, setColumnDimensions, setColumnMode, setSidebarMode } = this.props;

    return (
      <div className="dropdown workspace-dropdown">
        <header className="dropdown-header">
          <div className="header-controls">
            Your Workspace
            <span className="close">
              <a href="#"><i className="fa fa-times" onClick={close} /></a>
            </span>
          </div>
        </header>

        <ColumnMode
          currentMode={dpWindow.get('columnMode')}
          onChangeMode={setColumnMode}
          columnDimensions={dpWindow.get('columnDimensions')}
          onChangeDimensions={setColumnDimensions}
        />
        <SidebarMode currentMode={dpWindow.get('sidebarMode')} onChangeMode={setSidebarMode} />

        <div className="dpw-top-bar-dropdown-footer">
          <a href="#" className="dpw-top-bar-dropdown-button" onClick={saveWorkspace}>Save Workspace</a>
          <a href="#" className="dpw-top-bar-dropdown-button blank" onClick={resetAll}>Reset All</a>
        </div>

      </div>
    );
  }
}
