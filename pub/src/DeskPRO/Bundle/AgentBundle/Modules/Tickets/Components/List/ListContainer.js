import React, { Component, PropTypes } from 'react';
import { List } from './List';
import { connect } from 'react-redux';
import { isDoneSelector, viewModeSelector } from '../../Selectors/list';
import { unload } from '../../Actions/listActions';

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

  componentWillUnmount() {
    this.props.dispatch(unload());
  }

  render() {
    return <List {...this.props} />;
  }
}
