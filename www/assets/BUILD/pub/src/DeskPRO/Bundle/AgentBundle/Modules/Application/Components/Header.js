import PropTypes from 'prop-types';
import React from 'react';
import { HeaderWidget } from '../../IM/Components/HeaderWidget';
import { WorkspaceContainer } from './Workspace/WorkspaceContainer';
import { PersonAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar';

export class Header extends React.Component {

  static propTypes = {
    user:              PropTypes.object.isRequired,
    toggleWorkspace:   PropTypes.func.isRequired,
    togglePreferences: PropTypes.func.isRequired
  };

  getPositionTarget = () => this.refs.workspaceButton;

  render() {
    const { user, togglePreferences, toggleWorkspace } = this.props;

    return (
      <header className="dp-window-header top-bar">
        <a href="https://www.deskpro.com/" className="logo" />
        <HeaderWidget />

        <div className="user-options">
          <a href="#" className="notification-button" ref="workspaceButton" onClick={toggleWorkspace}>
            <span className="title">
              <i className="fa fa-columns" />
              <i className="fa fa-angle-down" />
            </span>
          </a>

          <a href="#" className="notification-button">
            <span className="notification-count">23</span>
            <span className="title">
              <i className="fa fa-cog" />
              {user.get('name')}
              <i className="fa fa-angle-down" />
            </span>
          </a>

          <a href="#" className="user-options-button" onClick={togglePreferences}>
            <PersonAvatar person={user} size={28} />
            <span className="title">Settings <i className="fa fa-angle-down" /></span>
          </a>
        </div>

        <WorkspaceContainer getPositionTarget={this.getPositionTarget} />
      </header>
    );
  }
}
