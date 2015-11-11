import React from 'react';
import { connect } from 'react-redux';
import { loadAllProjects } from '../../RecordStores/Actions/projectActions';
import { loadAllTaskLabels } from '../../RecordStores/Actions/taskLabelActions';
import { Nav } from './Nav';
import * as GroupsActions from '../../Actions/groupsActions';

@connect(state => ({
  dpWindow: state.Application.dpWindow
}))
export class NavContainer extends React.Component {

  constructor(props) {
    super(props);
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
    return <Nav {...this.props} />;
  }
}
