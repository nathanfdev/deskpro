import React, { PropTypes } from 'react';

import { ChangeModeButton } from './ChangeModeButton';

export class SidebarMode extends React.Component {
  static propTypes = {
    dpWindow: PropTypes.object.isRequired
  };

  changeMode = (mode) => {
    console.log('New SidebarMode', mode);
    console.log(mode);
  }

  render() {
    const { dpWindow } = this.props;
    const currentMode = dpWindow.get('columnMode');

    return (
      <div className="dpw-workspace-type-container">
        <div className="dpw-workspace-type-header">
          <span className="title">Sidebar Mode</span>
          <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor.</p>
        </div>

        <div className="dpw-workspace-state">
          <ChangeModeButton type={'static'}
                            title={'Static Mode'}
                            activeType={currentMode}
                            onChange={this.changeMode}>
            <span className="workspace-state-item state-sidebar active"></span>
            <span className="workspace-state-item left-column"></span>
            <span className="workspace-state-item right-column"></span>
          </ChangeModeButton>

          <ChangeModeButton type={'hover'}
                            title={'Hover Mode'}
                            activeType={currentMode}
                            onChange={this.changeMode}>
            <span className="active-workspace-mark"><i className="fa fa-check"></i></span>
            <span className="workspace-state-item state-sidebar state-sidebar-hover active"><i className="fa fa-asterisk"></i></span>
            <span className="workspace-state-item left-column"></span>
            <span className="workspace-state-item right-column"></span>
          </ChangeModeButton>
        </div>
      </div>);
  }
}
