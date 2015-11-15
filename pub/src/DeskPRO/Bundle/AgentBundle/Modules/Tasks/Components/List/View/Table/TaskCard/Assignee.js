import React, { PropTypes } from 'react';

export class Assignee extends React.Component {

  static propTypes = {
    task: PropTypes.object.isRequired,
    agents: PropTypes.object.isRequired,
    agentTeams: PropTypes.object.isRequired,
    departments: PropTypes.object.isRequired
  };

  render() {
    const { task, agents, agentTeams, departments } = this.props;
    let assignee = null;

    if (agents && task.get('agents').size) {
      assignee = agents.get(task.get('agents').first()).get('name');
    } else if (agentTeams && task.get('teams').size) {
      assignee = agentTeams.get(task.get('teams').first()).get('name');
    } else if (departments && task.get('departments').size) {
      assignee = departments.get(task.get('departments').first()).get('title');
    }

    return <span>{assignee}</span>;
  }
}
