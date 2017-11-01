import PropTypes from 'prop-types';
import React from 'react';
import { ContentContainer as ProfileContent } from './Tabs/Profile/ContentContainer';
import { ContentContainer as SignatureContent } from './Tabs/Signature/ContentContainer';
import { ContentContainer as SettingsContent } from './Tabs/Settings/ContentContainer';
import { ContentContainer as NotificationsContent } from './Tabs/Notifications/ContentContainer';
import { ContentContainer as DevicesContent } from './Tabs/Devices/ContentContainer';

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
