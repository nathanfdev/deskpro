import React from 'react';
import Immutable from 'immutable';
import { connect } from 'react-redux';
import { Groups } from './Groups';
import { groupsCountSelector } from '../../../Selectors/nav';

const options = {
  my: {filter: {assigned_agent: ['me']}, label: 'My Tasks'},
  team: {filter: {assigned_team: ['me']}, label: 'My Team Tasks'},
  department: {filter: {assigned_department: ['me']}, label: 'My Department Tasks'},
  delegated: {filter: {not_assigned_agent: ['me'], creator: 'me'}, label: 'My Delegated Tasks'},
  unassigned: {filter: {no_assignments: 1}, label: 'Unassigned Tasks'},
  all: {filter: {}, label: 'All Tasks'}
};

@connect(state => ({
  count: groupsCountSelector(state)
}))
export class GroupsContainer extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      count: props.count
    }
  }

  componentWillReceiveProps(props) {
    console.info(props.count.toJS());
    this.setState({count: props.count});
  }

  shouldComponentUpdate(props, state) {
    return !Immutable.is(this.state.count, state.count);
  }

  render() {
    return <Groups {...this.props} options={options} />;
  }
}
