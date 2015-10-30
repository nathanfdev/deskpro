import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { Content } from './Content';
import * as SettingsActions from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Actions/settingsActions';
import { mySelector, myStatusSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/RecordStores/Selectors/settingsSelectors';

@connect(state => ({
  settings: mySelector(state),
  settingStatus: myStatusSelector(state)
}))
export class ContentContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    settings: PropTypes.object.isRequired,
    profileStatus: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);
    props.dispatch(SettingsActions.loadMy('signature'));
  }

  render() {
    return (
      <div>
        <Content />
      </div>
    );
  }
}
