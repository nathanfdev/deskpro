import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import {
  PersonAvatar,
  DepartmentAvatar,
  AgentTeamAvatar
} from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar/index';

import { agentsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentsSelectors';
import { agentTeamsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentTeamsSelectors';
import { allDepartmentsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/departmentsSelectors';

@connect(state => ({
  agents: agentsSelector(state),
  agentTeams: agentTeamsSelector(state),
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
