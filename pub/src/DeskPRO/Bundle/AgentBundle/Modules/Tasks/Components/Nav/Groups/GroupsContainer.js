import React from 'react';
import { connect } from 'react-redux';
import { Groups } from './Groups';

const groupsOptions = {
  my: {filter: {agents: ['me']}, label: 'My Tasks'},
  team: {filter: {teams: ['me']}, label: 'My Team Tasks'},
  department: {filter: {departments: ['me']}, label: 'My Department Tasks'},
  delegated: {filter: {agents: ['not_me'], creator: 'me'}, label: 'My Delegated Tasks'},
  unassigned: {filter: {agents: ['null'], teams: ['null'], departments: ['null']}, label: 'Unassigned Tasks'},
  all: {filter: {done: 'all'}, label: 'All Tasks'}
};

@connect(state => ({
  groupsCount: state.Tasks.nav.get('groups')
}))
export class GroupsContainer extends React.Component {

  render() {
    return <Groups {...this.props} groupsOptions={groupsOptions} />;
  }
}
