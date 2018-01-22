import React from 'react';
import { connect } from 'react-redux';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';
import Settings from './Settings';

@connect(state => ({
  me: meSelector(state)
}))
class SettingsContainer extends React.Component {

  render() {
    return <Settings {...this.props} />;
  }
}

export default SettingsContainer;
