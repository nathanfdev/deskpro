import React from 'react';

export class Menu extends React.Component {
  render() {
    return (
      <div className="popup-sidebar" id="popup-sidebar">
        <div className="popup-sidebar-content">
          <ul>
            <li className="active"><a href="popup-user-preferences.html">Profile</a></li>
            <li><a href="popup-signature.html">Signature</a></li>
            <li><a href="popup-general-settings.html">Settings</a></li>
            <li>
              <a href="popup-notification-settings.html">Notifications</a>
              <ul className="stat-types-list">
                <li><a href="#">Inbox</a></li>
                <li><a href="#">Everything Else</a></li>
              </ul>
            </li>
            <li><a href="popup-devices.html">Devices</a></li>
          </ul>
        </div>
      </div>
    );
  }
}
