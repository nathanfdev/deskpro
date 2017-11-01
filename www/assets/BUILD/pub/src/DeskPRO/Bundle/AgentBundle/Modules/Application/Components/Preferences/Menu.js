import PropTypes from 'prop-types';
import React from 'react';
import { Menu as ProfileMenu } from './Tabs/Profile/Menu';
import { Menu as SignatureMenu } from './Tabs/Signature/Menu';
import { Menu as SettingsMenu } from './Tabs/Settings/Menu';
import { Menu as NotificationsMenu } from './Tabs/Notifications/Menu';
import { Menu as DevicesMenu } from './Tabs/Devices/Menu';
import * as AppActions from '../../Actions/appActions';
import classNames from 'classnames';

export class Menu extends React.Component {

  static propTypes = {
    dpWindow: PropTypes.object.isRequired,
    dispatch: PropTypes.func.isRequired
  };

  renderMenuItem(name, children) {
    const { dpWindow, dispatch } = this.props;
    const clickHandler = () => dispatch(AppActions.changePreferenceTab(name));

    return (
      <li className={classNames({ 'active': dpWindow.get('preferenceTab') === name })}
        onClick={clickHandler}
      >

        {children}
      </li>
    );
  }

  render() {
    return (
      <div className="popup-sidebar" id="popup-sidebar">
        <div className="popup-sidebar-content">
          <ul>
            {this.renderMenuItem('profile', (<ProfileMenu />))}
            {this.renderMenuItem('signature', (<SignatureMenu />))}
            {this.renderMenuItem('settings', (<SettingsMenu />))}
            {this.renderMenuItem('notifications', (<NotificationsMenu />))}
            {this.renderMenuItem('devices', (<DevicesMenu />))}
          </ul>
        </div>
      </div>
    );
  }
}
