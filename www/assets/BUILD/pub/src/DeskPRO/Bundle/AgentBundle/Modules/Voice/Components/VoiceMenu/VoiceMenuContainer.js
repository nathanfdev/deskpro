import React from 'react';
import { connect } from 'react-redux';
import { agentsSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/agents';
import VoiceMenu from './VoiceMenu';

@connect(state => ({
  agents: agentsSelector(state)
}))
class VoiceMenuContainer extends React.Component {

  render() {
    return <VoiceMenu {...this.props} />;
  }
}

export default VoiceMenuContainer;
