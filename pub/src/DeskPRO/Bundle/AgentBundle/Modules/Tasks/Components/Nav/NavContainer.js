import React from 'react';
import { connect } from 'react-redux';
import { loadAllAgents } from '../../../Agent/RecordStores/Actions/agentsActions';
import { loadAllProjects } from '../../RecordStores/Actions/projectActions';
import { loadAllTaskLabels } from '../../RecordStores/Actions/taskLabelActions';
import { agentsSelector } from '../../../Agent/RecordStores/Selectors/agentsSelectors';
import { allProjectsSelector } from '../../RecordStores/Selectors/projectSelectors';
import { allTaskLabelsSelector } from '../../RecordStores/Selectors/taskLabelSelectors';
import { Nav } from './Nav';
import * as GroupsActions from '../../Actions/groupsActions';

@connect(state => ({
  dpWindow: state.Application.dpWindow,
  agents: agentsSelector(state),
  projects: allProjectsSelector(state),
  labels: allTaskLabelsSelector(state),
  groups: state.Tasks.groups
}))
export class NavContainer extends React.Component {

  constructor(props) {
    super(props);

    props.dispatch(loadAllAgents());
    props.dispatch(loadAllProjects());
    props.dispatch(loadAllTaskLabels());

    props.dispatch(GroupsActions.loadAllTasksRemainingCount());
    props.dispatch(GroupsActions.loadMyTasksRemainingCount());
    props.dispatch(GroupsActions.loadTeamTasksRemainingCount());
    props.dispatch(GroupsActions.loadDepartmentTasksRemainingCount());
    props.dispatch(GroupsActions.loadDelegatedTasksRemainingCount());
    props.dispatch(GroupsActions.loadUnassignedTasksRemainingCount());
  }

  render() {
    return (
      <Nav {...this.props} />
    );
  }
}
