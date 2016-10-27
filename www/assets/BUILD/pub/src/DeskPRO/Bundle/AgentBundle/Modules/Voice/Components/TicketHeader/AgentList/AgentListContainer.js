import React from 'react';
import { connect } from 'react-redux';
import { agentsSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/agents';
import AgentList from './AgentList';

@connect(state => ({
  agents: agentsSelector(state)
}))
class AgentListContainer extends React.Component {

  render() {
    return <AgentList {...this.props} />;
  }
}

export default AgentListContainer;
