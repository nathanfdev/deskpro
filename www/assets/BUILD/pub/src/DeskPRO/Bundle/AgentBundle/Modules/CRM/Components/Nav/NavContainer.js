import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import * as actions from '../../Actions/crmNavActions';
import { Nav } from './Nav';

@connect(state => {
  return {
    isLoaded: state.CRM.nav.getIn(['async', 'done']),
    users: state.CRM.nav.get('users'),
    organizations: state.CRM.nav.get('organizations'),
    agents: state.CRM.nav.get('agents'),
    labels: state.CRM.nav.get('labels')
  };
})
export class NavContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired
  };

  componentDidMount() {
    this.props.dispatch(actions.initialLoad());
  }

  render() {
    return <Nav {...this.props} />;
  }
}
