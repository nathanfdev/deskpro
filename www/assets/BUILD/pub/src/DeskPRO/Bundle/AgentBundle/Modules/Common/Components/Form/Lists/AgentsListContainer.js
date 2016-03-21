import React, { Component, PropTypes } from 'react';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';
import { agentsSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/agents';
import { CollectionField } from '../../../../Common/Components/Popup';
import { AgentsList } from './AgentsList';

import { connect } from 'react-redux';
@connect(state => ({
  me: meSelector(state),
  agents: agentsSelector(state)
}))

export class AgentsListContainer extends Component {
  static propTypes = {
    onChange: PropTypes.func.isRequired,
    selfAssign: PropTypes.func,
    me: PropTypes.object.isRequired,
    agents: PropTypes.object.isRequired,
    multiple: PropTypes.bool,
    showOnlySelected: PropTypes.bool,
    filter: PropTypes.string,
    selected: PropTypes.object
  };

  render() {
    const { selected, agents, onChange, filter, multiple, showOnlySelected, selfAssign } = this.props;

    return (
      <CollectionField>
        <div part="title">
          Agent <a href="#" onClick={selfAssign}>Assign to me</a>
        </div>
        <AgentsList values={agents}
                    selected={selected}
                    filter={filter}
                    multiple={multiple}
                    showOnlySelected={showOnlySelected}
                    onChange={onChange}/>
      </CollectionField>
    );
  }
}