import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { loadAllAgents } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Actions/agentsActions';
import { loadAllProjects } from 'DeskPRO/Bundle/AgentBundle/Modules/OldTasks/RecordStores/Actions/projectActions';
import { agentsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Agent/RecordStores/Selectors/agentsSelectors';
import { allProjectsSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/OldTasks/RecordStores/Selectors/projectSelectors';
import { Nav } from './Nav';

@connect(state => ({
  dpWindow: state.Application.dpWindow,
  agents: agentsSelector(state),
  projects: allProjectsSelector(state)
}))
export class NavContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.object.isRequired,
    dpWindow: PropTypes.object.isRequired,
    agents: PropTypes.object.isRequired,
    projects: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);

    props.dispatch(loadAllAgents());
    props.dispatch(loadAllProjects());
  }

  render() {
    return (
      <Nav {...this.props} />
    );
  }
}
