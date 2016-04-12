import React, { Component, PropTypes } from 'react';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';
import { agentsSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/agents';
import { CollectionField } from '../../../../Common/Components/Popup/index';
import { AgentsList } from './AgentsList';
import { connect } from 'react-redux';

@connect(state => ({
  me:     meSelector(state),
  values: agentsSelector(state)
}))
export class AgentsListContainer extends Component {

  static propTypes = {
    selfAssign: PropTypes.func
  };

  render() {
    const { selfAssign } = this.props;

    return (
      <CollectionField>
        <div part="title">
          Agent <a href="#" onClick={selfAssign}>Assign to me</a>
        </div>
        <AgentsList param="agent" {...this.props} />
      </CollectionField>
    );
  }
}
