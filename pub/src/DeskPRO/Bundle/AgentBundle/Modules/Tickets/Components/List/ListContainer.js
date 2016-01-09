import React, { Component, PropTypes } from 'react';
import { List } from './List';
import { connect } from 'react-redux';
import { isDoneSelector, viewModeSelector } from '../../Selectors/list';
import { unload } from '../../Actions/listActions';
import { selectedCountSelector } from '../../Selectors/list';

@connect(state => ({
  selectedCount: selectedCountSelector(state),
  isDone: isDoneSelector(state),
  pagination: state.Tickets.list.get('pagination'),
  viewMode: viewModeSelector(state)
}))
export class ListContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    isDone: PropTypes.bool.isRequired,
    selectedCount: PropTypes.number.isRequired,
    pagination: PropTypes.object,
    viewMode: PropTypes.string.isRequired
  };

  componentWillUnmount() {
    this.props.dispatch(unload());
  }

  render() {
    return <List {...this.props} />;
  }
}
