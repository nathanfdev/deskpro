import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import * as AppActions from "../Actions/AppActions";
import { Link } from 'react-router';

@connect(state => ({
  user: state.Application.user,
  dp_window: state.Application.dp_window
}))
export class AppSwitcher extends React.Component {
  constructor(props) {
    super(props);
    this.expanded = false;
  }

  hoverSwitcher() {
    this.expanded = setTimeout(() => this.props.dispatch(AppActions.expandSwitcher()), 750);
  }

  cancelSwitcher() {
    clearTimeout(this.expanded);
    this.props.dispatch(AppActions.collapseSwitcher());
  }

  renderAppIcon(appId, title, iconClass) {
    const { dp_window, dispatch } = this.props;
    const clickHandler = () => dispatch(AppActions.setActiveApp(appId));
    const className = 'fa ' + iconClass;

    return (
      <li>
        <Link activeClassName="active" to={`${DP_BASE_URL_RELATIVE}/agent/${appId}`} onClick={clickHandler}>
          <i className={className}></i> <span className="title">{title}</span>
        </Link>
      </li>
    );
  }

  render() {
    const { dp_window, dispatch } = this.props;

    const my_classes = 'dp-app-switcher' + (dp_window.get('expandedSwitcher') ? ' expanded' : '');

    return (
    <nav onMouseEnter={this.hoverSwitcher.bind(this)}
      onMouseLeave={this.cancelSwitcher.bind(this)}
      className={my_classes}>
      <div className="app-bar">
        <ul>
          {this.renderAppIcon('tickets', 'Tickets', 'fa-envelope-o')}
          {this.renderAppIcon('crm', 'CRM', 'fa-users')}
          {this.renderAppIcon('chat', 'Chat', 'fa-comments-o')}
          {this.renderAppIcon('feedback', 'Feedback', 'fa-thumbs-up')}
          {this.renderAppIcon('publish', 'Publish', 'fa-edit')}
          {this.renderAppIcon('tasks', 'Tasks', 'fa-check-square-o')}
        </ul>
      </div>
    </nav>);
  }
}
