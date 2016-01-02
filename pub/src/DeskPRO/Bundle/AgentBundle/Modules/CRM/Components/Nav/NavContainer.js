import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import * as actions from '../../Actions/crmNavActions';
import { Nav } from './Nav';

@connect(state => {
  return {
    loaded: state.CRM.nav.getIn(['async', 'done']),
    dpWindow: state.Application.dpWindow,
    users: state.CRM.nav.get('users'),
    organizations: state.CRM.nav.get('organizations'),
    agents: state.CRM.nav.get('agents'),
    labels: state.CRM.nav.get('labels')
  };
})
export class NavContainer extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    loaded: PropTypes.bool.isRequired,
    dpWindow: PropTypes.object.isRequired,
    users: PropTypes.object.isRequired,
    organizations: PropTypes.object.isRequired,
    agents: PropTypes.object.isRequired,
    labels: PropTypes.object.isRequired
  };

  componentDidMount() {
    const { dispatch } = this.props;
    dispatch(actions.initialLoad());
  }

  render() {
    const {loaded, labels, users, organizations, agents, dpWindow, dispatch} = this.props;

    return (
      <Nav loaded={loaded}
           labels={labels}
           users={users}
           organizations={organizations}
           agents={agents}
           dispatch={dispatch}
           dpWindow={dpWindow}/>
    );
  }
}
