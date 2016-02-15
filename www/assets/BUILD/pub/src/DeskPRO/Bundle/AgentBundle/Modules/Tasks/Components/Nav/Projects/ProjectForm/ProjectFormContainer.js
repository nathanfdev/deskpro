import React from 'react';
import { connect } from 'react-redux';
import { ProjectForm } from './ProjectForm';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';
import { agentsSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/agents';
import { allSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

@connect(state => ({
  me: meSelector(state),
  agents: agentsSelector(state),
  agentTeams: allSelectorFactory('AgentTeam')(state),
  departments: allSelectorFactory('Department')(state)
}))
export class ProjectFormContainer extends React.Component {
  render() {
    return (
      <ProjectForm {...this.props} />
    );
  }
}
