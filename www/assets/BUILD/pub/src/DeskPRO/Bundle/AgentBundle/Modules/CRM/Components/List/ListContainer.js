import React, { Component } from 'react';
import { List } from './List';
import { currentViewModeSelector, currentContentSelector, isLoadedSelector, paginationSelector }
  from '../../Selectors/list';
import { selectedSelector } from '../../../Application/Selectors/massActions';

import { connect } from 'react-redux';
@connect(state => ({
  isLoaded:        isLoadedSelector(state),
  pagination:      paginationSelector(state),
  selected:        selectedSelector(state),
  currentViewMode: currentViewModeSelector(state),
  content:         currentContentSelector(state)
}))

export class ListContainer extends Component {
  render() {
    return (
      <List {...this.props} />
    );
  }
}
