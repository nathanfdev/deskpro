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
          <ChangeModeButton type={'Static'}
                            activeType={currentMode}
                            onChange={this.changeMode} />

          <ChangeModeButton type={'Hover'}
                            activeType={currentMode}
                            onChange={this.changeMode} />
        </div>
      </div>);
  }
}
