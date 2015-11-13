import React from 'react';
import { connect } from 'react-redux';
import { Groups } from './Groups';

const groupsOptions = {
  my: {filter: {assigned_agent: ['me']}, label: 'My Tasks'},
  team: {filter: {assigned_team: ['me']}, label: 'My Team Tasks'},
  department: {filter: {assigned_department: ['me']}, label: 'My Department Tasks'},
  delegated: {filter: {not_assigned_agent: ['me'], creator: 'me'}, label: 'My Delegated Tasks'},
  unassigned: {filter: {no_assignments: 1}, label: 'Unassigned Tasks'},
  all: {filter: {done: 1}, label: 'All Tasks'}
};

@connect(state => ({
  groupsCount: state.Tasks.nav.get('groups')
}))
export class GroupsContainer extends React.Component {

  render() {
    return <Groups {...this.props} groupsOptions={groupsOptions} />;
  }
}
