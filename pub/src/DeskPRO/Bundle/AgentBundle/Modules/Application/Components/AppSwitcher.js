import React, { PropTypes } from 'react';
import { connect } from 'redux/react';
import * as AppActions from "../Actions/AppActions";
import { Link } from 'react-router';

@connect(state => ({
  user: state.user,
  dp_window: state.dp_window
}))
export default class AppSwitcher extends React.Component {
  renderAppIcon(appId, title, iconClass) {
    const { dp_window, dispatch } = this.props;
    const clickHandler = () => dispatch(AppActions.setActiveApp(appId));
    const className = 'fa ' + iconClass;

    return (
      <li>
        <Link to={`/index.php/agent/${appId}`}>
          <i className={className}></i> <span className="title">{title}</span>
        </Link>
      </li>
    );
  }

  render() {
      const { dp_window, dispatch } = this.props;

      const my_classes = "dp-app-switcher" + (dp_window.expandedSwitcher ? ' expanded' : '');

    return (
    <nav className={my_classes}>
      <div className="app-bar">
        <ul>
          {this.renderAppIcon('tickets', 'Tickets', 'fa-envelope-o')}
          {this.renderAppIcon('users', 'Users', 'fa-users')}
          {this.renderAppIcon('chat', 'Chat', 'fa-comments-o')}
          {this.renderAppIcon('feedback', 'Feedback', 'fa-thumbs-up')}
          {this.renderAppIcon('publish', 'Publish', 'fa-edit')}
          {this.renderAppIcon('tasks', 'Tasks', 'fa-check-square-o')}
        </ul>
      </div>
      <a href="#" onClick={() => dispatch(AppActions.expandSwitcher())} className="app-switcher-size-toggle app-switcher-expand">&gt;&nbsp;&gt;&nbsp;&gt;</a>
      <a href="#" onClick={() => dispatch(AppActions.collapseSwitcher())} className="app-switcher-size-toggle app-switcher-collapse">&lt;&nbsp;&lt;&nbsp;&lt;</a>
    </nav>);
  }
}
