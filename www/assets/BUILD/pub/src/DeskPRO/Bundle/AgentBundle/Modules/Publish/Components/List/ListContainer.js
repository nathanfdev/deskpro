import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { List } from './List';
import { currentViewModeSelector, contentSelector, isLoadedSelector, paginationSelector } from '../../Selectors/list';
import { selectedSelector } from '../../../Application/Selectors/massActions';

@connect(state => {
  return ({
    content:         contentSelector(state),
    isLoaded:        isLoadedSelector(state),
    pagination:      paginationSelector(state),
    selected:        selectedSelector(state),
    currentViewMode: currentViewModeSelector(state)
  });
})
export class ListContainer extends Component {
  render = () => <List {...this.props} />;
}
