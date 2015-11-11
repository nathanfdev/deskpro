import React from 'react';
import { connect } from 'react-redux';
import { loadAllProjects } from '../../RecordStores/Actions/projectActions';
import { loadAllTaskLabels } from '../../RecordStores/Actions/taskLabelActions';
import { Nav } from './Nav';
import { initialLoad } from '../../Actions/navActions';

@connect(state => ({
  dpWindow: state.Application.dpWindow
}))
export class NavContainer extends React.Component {

  constructor(props) {
    super(props);
    props.dispatch(loadAllProjects());
    props.dispatch(loadAllTaskLabels());

    props.dispatch(initialLoad());
  }

  render() {
    return <Nav {...this.props} />;
  }
}
