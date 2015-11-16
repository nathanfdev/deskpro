import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { allProjectsSelector } from '../../../RecordStores/Selectors/projectSelectors';
import { allTaskListsSelector } from '../../../RecordStores/Selectors/taskListSelectors';
import { agentsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentsSelectors';
import { agentTeamsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentTeamsSelectors';
import { allDepartmentsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/departmentsSelectors';

@connect(state => ({
  lists: allTaskListsSelector(state),
  projects: allProjectsSelector(state),
  agents: agentsSelector(state),
  agentTeams: agentTeamsSelector(state),
  departments: allDepartmentsSelector(state)
}))
export class ListGroupTitleContainer extends React.Component {

  static propTypes = {
    title: PropTypes.string,
    lists: PropTypes.object.isRequired,
    projects: PropTypes.object.isRequired,
    agents: PropTypes.object.isRequired,
    agentTeams: PropTypes.object.isRequired,
    departments: PropTypes.object.isRequired
  };

  render() {
    const { lists, projects, agents, agentTeams, departments } = this.props;

    let title = this.props.title;
    if (typeof title === 'object') {
      const type = title.first();
      const id = title.last();

      title = `${type}, ${id}`;
      switch (type) {
        case 'list':
          const list = lists.get(id);
          title = list ? list.get('title') : title;

          break;
        case 'project':
          const project = projects.get(id);
          title = project ? project.get('title') : title;

          break;
        case 'agent':
          const agent = agents.get(id);
          title = agent ? agent.get('name') : title;

          break;
        case 'team':
          const team = agentTeams.get(id);
          title = team ? team.get('name') : title;

          break;
        case 'department':
          const department = departments.get(id);
          title = department ? department.get('title') : title;

          break;
        default:
          break;
      }
    }

    return (
      <span>{title}</span>
    );
  }
}
