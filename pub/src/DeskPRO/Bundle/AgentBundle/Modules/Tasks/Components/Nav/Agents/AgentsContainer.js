import React from 'react';
import { connect } from 'react-redux';
import { Agents } from './Agents';
import { agentsSelector } from '../../../../Agent/RecordStores/Selectors/agentsSelectors';

@connect(state => ({
  agents: agentsSelector(state),
  agentsCount: state.Tasks.nav.get('agents')
}))
export class AgentsContainer extends React.Component {

  render() {
    return <Agents {...this.props} />;
  }
}
