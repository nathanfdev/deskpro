import React from 'react';
import { Menu as ProfileMenu } from './Tabs/Profile/Menu';
import { Menu as SignatureMenu } from './Tabs/Signature/Menu';
import { Menu as SettingsMenu } from './Tabs/Settings/Menu';
import { Menu as NotificationsMenu } from './Tabs/Notifications/Menu';
import { Menu as DevicesMenu } from './Tabs/Devices/Menu';

export class Menu extends React.Component {
  render() {
    return (
      <div className="popup-sidebar" id="popup-sidebar">
        <div className="popup-sidebar-content">
          <ul>
            <li className="active"><ProfileMenu /></li>
            <li><SignatureMenu /></li>
            <li><SettingsMenu /></li>
            <li><NotificationsMenu /></li>
            <li><DevicesMenu /></li>
          </ul>
        </div>
      </div>
    );
  }
}
