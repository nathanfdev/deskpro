import React, { PropTypes } from 'react';

export default class AppSwitcher extends React.Component {
  static propTypes = {
    switchApp: PropTypes.func.isRequired,
    activeAppId: PropTypes.string.isRequired
  }

  renderAppIcon(appId, title, iconClass) {
    const { activeAppId } = this.props;
    const clickHandler = () => this.props.switchApp(appId);
    const className = 'fa ' + iconClass;
    const rowClassName = activeAppId === appId ? ' active' : '';

    return (<li>
      <a onClick={clickHandler} className={rowClassName}>
        <i className={className}></i>
      </a>
    </li>);
  }

  render() {
    return (<nav className="dp-app-switcher">
      <div className="app-bar">
        <ul>
          {this.renderAppIcon('tickets', 'Tickets', 'fa-envelope-o')}
          {this.renderAppIcon('users', 'Users', 'fa-users')}
          {this.renderAppIcon('feedback', 'Feedback', 'fa-thumbs-up')}
          {this.renderAppIcon('publish', 'Publish', 'fa-edit')}
          {this.renderAppIcon('tasks', 'Tasks', 'fa-check-square-o')}
        </ul>
      </div>
    </nav>);
  }
}
