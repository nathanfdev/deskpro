import React from 'react';
import { connect } from 'react-redux';
import { loadAllProjects } from '../../RecordStores/Actions/projectActions';
import { loadAllTaskLabels } from '../../RecordStores/Actions/taskLabelActions';
import { loadAllTaskLists } from '../../RecordStores/Actions/taskListActions';
import { Nav } from './Nav';
import { initialLoad } from '../../Actions/navActions';
import { isDoneSelector } from '../../Selectors/nav';

@connect(state => ({
  dpWindow: state.Application.dpWindow,
  isDone: isDoneSelector(state)
}))
export class NavContainer extends React.Component {

  constructor(props) {
    super(props);

    props.dispatch(loadAllProjects());
    props.dispatch(loadAllTaskLabels());
    props.dispatch(loadAllTaskLists());

    props.dispatch(initialLoad());
  }

  render() {
    return <Nav {...this.props} />;
  }
}
