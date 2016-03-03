import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';
import { agentsSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/agents';
import { allSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { AssignForm } from './AssignForm';


@connect(state => ({
  me: meSelector(state),
  agents: agentsSelector(state),
  agentTeams: allSelectorFactory('AgentTeam')(state),
  departments: allSelectorFactory('Department')(state)
}))

export class AssignFormContainer extends React.Component {

  render() {
    return(
      <AssignForm {...this.props} />
    );
  }
}
