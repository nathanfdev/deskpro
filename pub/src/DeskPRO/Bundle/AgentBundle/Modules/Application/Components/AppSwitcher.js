import React, { PropTypes } from 'react';
import * as AppActions from '../../Application/Actions/AppActions';
import { Link } from 'react-router';

export class AppSwitcher extends React.Component {

  static propTypes = {
    dpWindow: PropTypes.object.isRequired,
    dispatch: PropTypes.func.isRequired
  };

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
    const { dispatch } = this.props;
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
    const { dpWindow } = this.props;
    const classesNames = ['dp-app-switcher'];

    if (dpWindow.get('expandedSwitcher')) {
      classesNames.push('expanded');
    }

    return (
      <nav onMouseEnter={this.hoverSwitcher.bind(this)}
        onMouseLeave={this.cancelSwitcher.bind(this)}
        className={classesNames.join(' ')}>


        <div className="dpw-app-bar dpw-app-bar-state-1">
          <ul className="app-list">
            <li>
              <a href="#" className="dpw-app-bar-item-1 active">
                <span className="dpw-app-bar-notification">33</span>
                <div className="dpw-app-bar-icon">
                  <div className="icon icon-dp-streamline-mail-2"></div>
                </div>
              </a>
            </li>

            <li>
              <a href="#" className="dpw-app-bar-item-2">
                <div className="dpw-app-bar-icon">
                  <div className="icon icon-dp-streamline-bubble-conversation-4"></div>
                </div>
              </a>
            </li>

            <li>
              <a href="#" className="dpw-app-bar-item-3">
                <span className="dpw-app-bar-notification">33</span>
                <div className="dpw-app-bar-icon">
                  <div className="icon icon-dp-streamline-connection-2"></div>
                </div>
              </a>
            </li>

            <li>
              <a href="#" className="dpw-app-bar-item-4">
                <div className="dpw-app-bar-icon">
                  <div className="icon icon-dp-streamline-hand-like-2"></div>
                </div>
              </a>
            </li>

            <li>
              <a href="#" className="dpw-app-bar-item-5">
                <div className="dpw-app-bar-icon">
                  <div className="icon icon-dp-streamline-edit-1"></div>
                </div>
              </a>
            </li>

            <li>
              <a href="#" className="dpw-app-bar-item-6">
                <div className="dpw-app-bar-icon">
                  <div className="icon icon-dp-streamline-check-circle-2"></div>
                </div>
              </a>
            </li>

          </ul>
        </div>
      </nav>
    );
  }
}
