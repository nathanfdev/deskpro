import React, { PropTypes } from 'react';
import * as AppActions from '../../Application/Actions/appActions';
import { Link } from 'react-router';

export class AppSwitcher extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);
    this.expandTimeout = false;
    this.state = {
      expandedSwitcher: false
    };
  }

  hoverSwitcher = () => {
    this.expandTimeout = setTimeout(() => this.expandSwitcher(), 250);
  };

  expandSwitcher() {
    const oldState = this.state;
    const newState = {...oldState};
    newState.expandedSwitcher = true;
    this.setState(newState);
  }

  cancelSwitcher = () => {
    clearTimeout(this.expandTimeout);
    const oldState = this.state;
    const newState = {...oldState};
    newState.expandedSwitcher = false;
    this.setState(newState);
  };

  renderAppIcon(appId, title, linkClass, iconClass, notificationCount = 0) {
    const { dispatch } = this.props;
    const clickHandler = () => dispatch(AppActions.setActiveApp(appId));
    const iconClassNames = 'icon ' + iconClass;

    return (
      <li>
        <Link className={linkClass} activeClassName="active" to={`${DP_BASE_URL_RELATIVE}/agent/${appId}`} onClick={clickHandler}>
          {notificationCount > 0 ? (<span className="dpw-app-bar-notification">{notificationCount}</span>) : null}
          <div className="dpw-app-bar-icon">
            <div className={iconClassNames}></div>
          </div>
          {this.state.expandedSwitcher ? (<span className="dps-app-bar-title">{title}</span>) : null }
        </Link>
      </li>
    );
  }

  render() {
    const classesNames = ['dpw-app-bar', 'dpw-app-bar-state-1'];
    if (this.state.expandedSwitcher) {
      classesNames.push('dpw-app-bar-expanded');
    }

    return (
      <nav className="dp-app-switcher" onMouseEnter={this.hoverSwitcher} onMouseLeave={this.cancelSwitcher}>
        <div className={classesNames.join(' ')}>
          <ul className="app-list">
            {this.renderAppIcon('tickets', 'Tickets', 'dpw-app-bar-item-1', 'icon-dp-streamline-mail-2', 15)}
            {this.renderAppIcon('crm', 'CRM', 'dpw-app-bar-item-2', 'icon-dp-streamline-connection-2')}
            {this.renderAppIcon('chat', 'Chat', 'dpw-app-bar-item-3', 'icon-dp-streamline-bubble-conversation-4', 2)}
            {this.renderAppIcon('feedback', 'Feedback', 'dpw-app-bar-item-4', 'icon-dp-streamline-hand-like-2')}
            {this.renderAppIcon('publish', 'Publish', 'dpw-app-bar-item-5', 'icon-dp-streamline-edit-1')}
            {this.renderAppIcon('tasks', 'Tasks', 'dpw-app-bar-item-6', 'icon-dp-streamline-check-circle-2')}
          </ul>
        </div>
      </nav>
    );
  }
}
