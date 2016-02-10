import React from 'react';
import { connect } from 'react-redux';
import { ProjectForm } from './ProjectForm';
import { meSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/RecordStores/Selectors/meSelectors';
import { agentsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentsSelectors';
import { allDepartmentsSelector, allAgentTeamsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/RecordsStore';

@connect(state => ({
  me: meSelector(state),
  agents: agentsSelector(state),
  agentTeams: allAgentTeamsSelector(state),
  departments: allDepartmentsSelector(state)
}))
export class ProjectFormContainer extends React.Component {
  render() {
    return (
      <ProjectForm {...this.props} />
    );
  }
}
