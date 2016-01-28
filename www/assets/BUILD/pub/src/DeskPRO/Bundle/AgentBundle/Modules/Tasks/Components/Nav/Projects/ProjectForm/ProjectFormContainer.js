import React from 'react';
import { connect } from 'react-redux';
import { ProjectForm } from './ProjectForm';
import { meSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/RecordStores/Selectors/meSelectors';
import { agentsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentsSelectors';
import { agentTeamsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentTeamsSelectors';
import { allDepartmentsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/departmentsSelectors';

@connect(state => ({
  me: meSelector(state),
  agents: agentsSelector(state),
  agentTeams: agentTeamsSelector(state),
  departments: allDepartmentsSelector(state)
}))
export class ProjectFormContainer extends React.Component {
  render() {
    return (
      <ProjectForm {...this.props} />
    );
  }
}
