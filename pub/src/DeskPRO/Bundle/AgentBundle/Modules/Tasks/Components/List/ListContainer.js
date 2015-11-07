import React from 'react';
import { connect } from 'react-redux';
import { List } from './List';

@connect(state => ({
  tasks: state.Tasks.tasks.get('elements'),
  loaded: state.Tasks.tasks.getIn(['async', 'done'])
}))
export class ListContainer extends React.Component {

  render() {
    return (
      <List {...this.props} />
    );
  }
}
