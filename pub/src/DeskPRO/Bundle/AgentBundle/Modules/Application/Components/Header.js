import React, { PropTypes } from 'react';
import { HeaderWidget } from '../../IM/Components/HeaderWidget';
import { WorkspaceContainer } from './Workspace/WorkspaceContainer';
import { PersonAvatar } from '../../Common/Components/Avatar/index';
import * as AppActions from '../Actions/AppActions';

export class Header extends React.Component {

  static propTypes = {
    user: PropTypes.object.isRequired,
    dispatch: PropTypes.func.isRequired
  };

  toggleWorkspace = event => {
    event.preventDefault();
    this.props.dispatch(AppActions.toggleWorkspace());
  };

  togglePreferences = event => {
    event.preventDefault();
    this.props.dispatch(AppActions.togglePreferences());
  };

  render() {
    const { user } = this.props;

    return (
        <header className="dp-window-header top-bar">
          <a href="https://www.deskpro.com/" className="logo"></a>
          <HeaderWidget/>

          <div className="user-options">
            <a href="#" className="notification-button" ref="workspaceButton" onClick={this.toggleWorkspace}>
              <span className="title" ><i className="fa fa-columns"></i><i className="fa fa-angle-down"></i></span>
            </a>

            <a href="#" className="notification-button">
              <span className="notification-count">23</span>
              <span className="title"><i className="fa fa-cog"></i> Admin <i className="fa fa-angle-down"></i></span>
            </a>

            <a href="#" className="user-options-button" onClick={this.togglePreferences}>
              <PersonAvatar person={user} size="28" />
              <span className="title">Settings <i className="fa fa-angle-down"></i></span>
            </a>
          </div>

        <WorkspaceContainer positionTarget={this.refs.workspaceButton} />
      </header>
    );
  }
}
