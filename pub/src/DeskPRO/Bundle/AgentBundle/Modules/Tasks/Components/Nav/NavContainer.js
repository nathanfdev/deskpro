import React from 'react';
import { connect } from 'react-redux';
import { loadAllAgents } from '../../../Agent/RecordStores/Actions/agentsActions';
import { loadAllProjects } from '../../RecordStores/Actions/projectActions';
import { loadAllTaskLabels } from '../../RecordStores/Actions/taskLabelActions';
import { agentsSelector } from '../../../Agent/RecordStores/Selectors/agentsSelectors';
import { allProjectsSelector } from '../../RecordStores/Selectors/projectSelectors';
import { allTaskLabelsSelector } from '../../RecordStores/Selectors/taskLabelSelectors';
import { Nav } from './Nav';

@connect(state => ({
  dpWindow: state.Application.dpWindow,
  agents: agentsSelector(state),
  projects: allProjectsSelector(state),
  labels: allTaskLabelsSelector(state)
}))
export class NavContainer extends React.Component {

  constructor(props) {
    super(props);

    props.dispatch(loadAllAgents());
    props.dispatch(loadAllProjects());
    props.dispatch(loadAllTaskLabels());
  }

  render() {
    return (
      <Nav {...this.props} />
    );
  }
}
