import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { loadAllProjects } from 'DeskPRO/Bundle/AgentBundle/Modules/OldTasks/RecordStores/Actions/projectActions';
import { allProjectsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/OldTasks/RecordStores/Selectors/projectSelectors';
import { Nav } from './Nav';

@connect(state => ({
  dpWindow: state.Application.dpWindow,
  projects: allProjectsSelector(state)
}))
export class NavContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.object.isRequired,
    dpWindow: PropTypes.object.isRequired,
    projects: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);
    props.dispatch(loadAllProjects());
  }

  render() {
    return (
      <Nav {...this.props} />
    );
  }
}
