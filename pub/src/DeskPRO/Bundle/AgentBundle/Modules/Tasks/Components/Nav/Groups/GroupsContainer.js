import React from 'react';
import { connect } from 'react-redux';
import { Groups } from './Groups';

@connect(state => ({
  groupsState: state.Tasks.groups
}))
export class GroupsContainer extends React.Component {

  render() {
    return <Groups {...this.props} />;
  }
}
