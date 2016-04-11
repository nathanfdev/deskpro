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
    dispatch:        PropTypes.func.isRequired,
    handlePageClick: PropTypes.func.isRequired,
    selected:        PropTypes.object.isRequired,
    currentViewMode: PropTypes.string.isRequired,
    content:         PropTypes.string.isRequired,
    isLoaded:        PropTypes.bool,
    pagination:      PropTypes.object
  };

  render() {
    const handlePageClick = page => this.props.dispatch(applyParams({ page }));

    return (
      <List {...this.props} handlePageClick={handlePageClick} />
    );
  }
}
