import React, { Component, PropTypes } from 'react';
import { List } from './List';
import { connect } from 'react-redux';
import { isDoneSelector, viewModeSelector } from '../../Selectors/list';

@connect(state => ({
  isDone: isDoneSelector(state),
  viewMode: viewModeSelector(state)
}))
export class ListContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    isDone: PropTypes.bool.isRequired,
    viewMode: PropTypes.string.isRequired
  };

  render() {
    return <List {...this.props} />;
  }
}
