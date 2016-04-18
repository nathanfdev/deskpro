import React from 'react';
import { connect } from 'react-redux';
import { Agents } from './Agents';
import { agentsSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/agents';
import { agentsCountMapSelector } from '../../../Selectors/nav';

@connect(state => ({
  agents:         agentsSelector(state),
  agentsCountMap: agentsCountMapSelector(state)
}))
export class AgentsContainer extends React.Component {

  render() {
    return <Agents {...this.props} />;
  }
}
