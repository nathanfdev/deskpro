import React, { Component, PropTypes } from 'react';
import { List } from './List';
import { connect } from 'react-redux';
import { isLoadedSelector, viewModeSelector, paginationSelector } from '../../Selectors/list';
import { unload } from '../../Actions/listActions';

@connect(state => ({
  isLoaded: isLoadedSelector(state),
  pagination: paginationSelector(state),
  viewMode: viewModeSelector(state)
}))
export class ListContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    isLoaded: PropTypes.bool.isRequired,
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
