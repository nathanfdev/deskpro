import React, { Component, PropTypes } from 'react';
import { agentsSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/agents';
import { paramsSelector } from '../../../../Application/Selectors/massActions';
import { CollectionField } from '../../../../Common/Components/Popup';
import { AgentsList } from './AgentsList';

import { connect } from 'react-redux';
@connect(state => ({
  agents: agentsSelector(state),
  currentParams: paramsSelector(state)
}))

export class AgentsListContainer extends Component {
  static propTypes = {
    onChange: PropTypes.func.isRequired,
    agents: PropTypes.object.isRequired,
    currentParams: PropTypes.object
  };

  render() {
    const { currentParams, agents, onChange } = this.props;
    const assign = currentParams.get('assign');

    return (
      <CollectionField>
        <div part="title">
          Agent <a href="#" onClick={this.onAssignSelf}>Assign to me</a>
        </div>
        <AgentsList values={agents}
                    selected={assign && assign.get('agent')}
                    onChange={onChange}/>
      </CollectionField>
    );
  }
}