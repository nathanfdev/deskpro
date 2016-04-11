import React, { Component, PropTypes } from 'react';
import { List } from './List';
import { currentViewModeSelector, currentContentSelector, isLoadedSelector, paginationSelector }
  from '../../Selectors/list';
import { selectedSelector } from '../../../Application/Selectors/massActions';
import { applyParams } from '../../Actions/crmListActions';
import { connect } from 'react-redux';

@connect(state => ({
  isLoaded:        isLoadedSelector(state),
  pagination:      paginationSelector(state),
  selected:        selectedSelector(state),
  currentViewMode: currentViewModeSelector(state),
  content:         currentContentSelector(state)
}))

export class ListContainer extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired
  };

  render() {
    const handlePageClick = page => this.props.dispatch(applyParams({ page }));

    return (
      <List {...this.props} handlePageClick={handlePageClick} />
    );
  }
}
