import React from 'react';
import { Content as ProfileContent } from './Tabs/Profile/Content';

export class Content extends React.Component {
  render() {
    return (
      <div className="popup-content">
        <ProfileContent />
      </div>
    );
  }
}
