import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { PersonAvatar, DepartmentAvatar, AgentTeamAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar';
import { agentsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentsSelectors';
import { allDepartmentsSelector, allAgentTeamsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/RecordsStore';

@connect(state => ({
  agents: agentsSelector(state),
  agentTeams: allAgentTeamsSelector(state),
  departments: allDepartmentsSelector(state)
}))
export class AssigneeAvatar extends React.Component {
  static propTypes = {
    task: PropTypes.object.isRequired
  };

  render() {
    const { task, agents, agentTeams, departments } = this.props;

    if (agents && task.get('agents').size) {
      return <PersonAvatar person={agents.get(task.get('agents').first())} size={16} />;
    } else if (agentTeams && task.get('teams').size) {
      return <AgentTeamAvatar agentTeam={agentTeams.get(task.get('teams').first())} size={16} />;
    } else if (departments && task.get('departments').size) {
      return <DepartmentAvatar department={departments.get(task.get('departments').first())} size={16} />;
    }

    return null;
  }
}
