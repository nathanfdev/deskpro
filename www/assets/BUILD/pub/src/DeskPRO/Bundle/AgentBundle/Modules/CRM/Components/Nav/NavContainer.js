import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { isLoadedSelector, usersSelector, organizationsSelector, agentsSelector, labelsSelector }
  from '../../Selectors/nav';
import * as actions from '../../Actions/crmNavActions';
import { Nav } from './Nav';

@connect(state => ({
  isLoaded: isLoadedSelector(state),
  users: usersSelector(state),
  organizations: organizationsSelector(state),
  agents: agentsSelector(state),
  labels: labelsSelector(state)
}))

export class NavContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired
  };

  componentDidMount() {
    this.props.dispatch(actions.initialLoad());
  }

  render() {
    return <Nav {...this.props}/>;
  }
}
