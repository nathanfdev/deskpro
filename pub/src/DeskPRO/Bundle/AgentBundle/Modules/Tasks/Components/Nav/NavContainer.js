import React from 'react';
import { connect } from 'react-redux';
import { loadAllProjects } from '../../RecordStores/Actions/projectActions';
import { loadAllTaskLabels } from '../../RecordStores/Actions/taskLabelActions';
import { Nav } from './Nav';
import * as NavActions from '../../Actions/navActions';

@connect(state => ({
  dpWindow: state.Application.dpWindow
}))
export class NavContainer extends React.Component {

  constructor(props) {
    super(props);
    props.dispatch(loadAllProjects());
    props.dispatch(loadAllTaskLabels());

    props.dispatch(NavActions.loadAllTasksRemainingCount());
    props.dispatch(NavActions.loadMyTasksRemainingCount());
    props.dispatch(NavActions.loadTeamTasksRemainingCount());
    props.dispatch(NavActions.loadDepartmentTasksRemainingCount());
    props.dispatch(NavActions.loadDelegatedTasksRemainingCount());
    props.dispatch(NavActions.loadUnassignedTasksRemainingCount());
  }

  render() {
    return <Nav {...this.props} />;
  }
}
