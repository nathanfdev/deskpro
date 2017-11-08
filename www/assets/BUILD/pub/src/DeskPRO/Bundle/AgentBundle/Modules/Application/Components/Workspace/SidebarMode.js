import PropTypes from 'prop-types';
import React from 'react';
import { ChangeModeButton } from './ChangeModeButton';

export class SidebarMode extends React.Component {

  static propTypes = {
    currentMode:  PropTypes.string.isRequired,
    onChangeMode: PropTypes.func.isRequired
  };

  render() {
    const { currentMode, onChangeMode } = this.props;

    return (
      <div className="dpw-workspace-type-container">
        <div className="dpw-workspace-type-header">
          <span className="title">Sidebar Mode</span>
          <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor.</p>
        </div>

        <div className="dpw-workspace-state">
          <ChangeModeButton
            type={'static'}
            title={'Static Mode'}
            activeType={currentMode}
            onChange={onChangeMode}
          >

            <span className="workspace-state-item state-sidebar active" />
            <span className="workspace-state-item left-column" />
            <span className="workspace-state-item right-column" />
          </ChangeModeButton>

          <ChangeModeButton
            type={'hover'}
            title={'Hover Mode'}
            activeType={currentMode}
            onChange={onChangeMode}
          >

            <span className="workspace-state-item state-sidebar state-sidebar-hover active">
              <i className="fa fa-asterisk" />
            </span>
            <span className="workspace-state-item left-column" />
            <span className="workspace-state-item right-column" />
          </ChangeModeButton>
        </div>
      </div>);
  }
}
