import PropTypes from 'prop-types';
import React from 'react';
import * as AppActions from '../../Application/Actions/appActions';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { Link } from 'react-router';
import classNames from 'classnames';

export class AppSwitcher extends React.Component {

  static propTypes = {
    switchApp:  PropTypes.func.isRequired,
    currentApp: PropTypes.string.isRequired
  };

  constructor(props) {
    super(props);

    this.expandTimeout = false;
    this.state = {
      expandedSwitcher: false
    };
  }

  hoverSwitcher = () => {
    this.expandTimeout = setTimeout(this.expandSwitcher, 250);
  };

  expandSwitcher = () => {
    this.setState({
      expandedSwitcher: true
    });
  };

  cancelSwitcher = () => {
    clearTimeout(this.expandTimeout);
    this.setState({
      expandedSwitcher: false
    });
  };

  renderAppIcon(appId, title, linkClass, iconClass, notificationCount = 0) {
    const { switchApp } = this.props;
    const clickHandler = () => switchApp(appId);
    const iconClassNames = 'icon ' + iconClass;

    return (
      <li>
        <Link className={linkClass}
          activeClassName="active"
          to={`${DP_BASE_URL_RELATIVE}/${DP_AGENT_INTERFACE_PATH_NAMESPACE}/${appId}`}
          onClick={clickHandler}
        >
          {notificationCount > 0 ? (<span className="dpw-app-bar-notification">{notificationCount}</span>) : null}
          <div className="dpw-app-bar-icon">
            <div className={iconClassNames}></div>
          </div>
          {this.state.expandedSwitcher ? (<span className="dps-app-bar-title">{title}</span>) : null}
        </Link>
      </li>
    );
  }

  render() {
    const { currentApp } = this.props;
    const classes = classNames('dpw-app-bar', { 'dpw-app-bar-expanded': this.state.expandedSwitcher });

    return (
      <nav className="dp-app-switcher" onMouseEnter={this.hoverSwitcher} onMouseLeave={this.cancelSwitcher}>
        <div className={classes} style={{ borderRightColor: constants.APP_COLOURS[currentApp] }}>
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
