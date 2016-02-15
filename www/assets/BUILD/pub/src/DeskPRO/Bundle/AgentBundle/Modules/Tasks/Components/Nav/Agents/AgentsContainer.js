import React from 'react';
import { connect } from 'react-redux';
import { Agents } from './Agents';
import { agentsSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/agents';
import { agentsCountSelector } from '../../../Selectors/nav';

@connect(state => ({
  agents: agentsSelector(state),
  agentsCount: agentsCountSelector(state)
}))
export class AgentsContainer extends React.Component {
  render() {
    return <Agents {...this.props} />;
  }
}
