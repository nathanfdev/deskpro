import React, { PropTypes } from 'react';
import { Content as ProfileContent } from './Tabs/Profile/Content';
import { Content as SignatureContent } from './Tabs/Signature/Content';
import { Content as SettingsContent } from './Tabs/Settings/Content';
import { Content as NotificationsContent } from './Tabs/Notifications/Content';
import { Content as DevicesContent } from './Tabs/Devices/Content';

export class Content extends React.Component {

  static propTypes = {
    dpWindow: PropTypes.object.isRequired
  };

  renderTabContent(name) {
    switch (name) {
      case 'profile':
        return (<ProfileContent />);
      case 'signature':
        return (<SignatureContent />);
      case 'settings':
        return (<SettingsContent />);
      case 'notifications':
        return (<NotificationsContent />);
      case 'devices':
        return (<DevicesContent />);
      default:
        return null;
    }
  }

  render() {
    const { dpWindow } = this.props;

    return (
      <div className="popup-content">
        {this.renderTabContent(dpWindow.get('preferenceTab'))}
      </div>
    );
  }
}
