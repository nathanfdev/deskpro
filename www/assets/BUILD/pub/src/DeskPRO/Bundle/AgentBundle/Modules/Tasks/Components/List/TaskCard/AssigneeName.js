import PropTypes from 'prop-types';
import React from 'react';

export class AssigneeName extends React.Component {

  static propTypes = {
    task:        PropTypes.object.isRequired,
    agents:      PropTypes.object,
    agentTeams:  PropTypes.object,
    departments: PropTypes.object
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
