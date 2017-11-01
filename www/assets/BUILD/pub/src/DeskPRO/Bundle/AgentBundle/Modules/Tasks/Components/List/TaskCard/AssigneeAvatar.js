import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { PersonAvatar, DepartmentAvatar, AgentTeamAvatar } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Avatar';
import { agentsSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/agents';
import { collectionSelectorFactory, allSelectorFactory } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import Immutable from 'immutable';

@connect(state => ({
  agents:      agentsSelector(state),
  agentTeams:  allSelectorFactory('AgentTeam')(state),
  departments: collectionSelectorFactory('Department', 'all_tickets')(state)
}))
export class AssigneeAvatar extends React.Component {
  static propTypes = {
    task:        PropTypes.object.isRequired,
    agents:      PropTypes.object.isRequired,
    agentTeams:  PropTypes.object.isRequired,
    departments: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);
    this.state = {
      task: props.task
    };
  }

  componentWillReceiveProps(props) {
    this.setState({
      task: props.task
    });
  }

  shouldComponentUpdate(props, state) {
    return !Immutable.is(this.state.task, state.task);
  }

  render() {
    const { task, agents, agentTeams, departments } = this.props;
    let title;
    let el;

    if (agents && task.get('agents').size) {
      const agent = agents.get(task.get('agents').first());
      title = `Agent: ${agent.get('name')}`;
      el = <PersonAvatar person={agent} size={16} />;
    } else if (agentTeams && task.get('teams').size) {
      const team = agentTeams.get(task.get('teams').first());
      title = `Team: ${team.get('title')}`;
      el = <AgentTeamAvatar agentTeam={team} size={16} />;
    } else if (departments && task.get('departments').size) {
      const dep = departments.get(task.get('departments').first());
      title = `Department: ${dep.get('title')}`;
      el = <DepartmentAvatar department={dep} size={16} />;
    }

    return el
      ? <div title={title}>{el}</div>
      : null;
  }
}
