import React, { Component, PropTypes } from 'react';
import { List } from './List';
import { connect } from 'react-redux';
import { isDoneSelector } from '../../Selectors/list';

@connect(state => ({
  isDone: isDoneSelector(state),
  mode: 'table'
}))
export class ListContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    isDone: PropTypes.bool.isRequired,
    mode: PropTypes.string.isRequired
  };

  render() {
    return <List {...this.props} />;
  }
}
