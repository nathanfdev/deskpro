import React, { PropTypes } from 'react';
import {
  PersonAvatar,
  DepartmentAvatar,
  AgentTeamAvatar
} from 'DeskPRO/Component/Avatar/index';

export class AssigneeAvatar extends React.Component {

  static propTypes = {
    task: PropTypes.object.isRequired,
    agents: PropTypes.object,
    agentTeams: PropTypes.object,
    departments: PropTypes.object
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
