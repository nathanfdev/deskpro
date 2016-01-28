import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { loadAllProjects } from '../../RecordStores/Actions/projectActions';
import { loadAllTaskLabels } from '../../RecordStores/Actions/taskLabelActions';
import { loadAllTaskLists } from '../../RecordStores/Actions/taskListActions';
import { Nav } from './Nav';
import { initialLoad } from '../../Actions/navActions';
import { isLoadedSelector } from '../../Selectors/nav';

@connect(state => ({
  dpWindow: state.Application.dpWindow,
  isLoaded: isLoadedSelector(state)
}))
export class NavContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired
  };

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
