import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { connect } from 'react-redux';
import { List } from './List';
import {
  currentViewModeSelector,
  contentSelector,
  isLoadedSelector,
  paginationSelector,
  currentListParamsSelector,
  fieldsSelector
} from '../../Selectors/list';
import { selectedSelector } from '../../../Application/Selectors/massActions';

@connect(
  state => ({
    content:           contentSelector(state),
    isLoaded:          isLoadedSelector(state),
    pagination:        paginationSelector(state),
    selected:          selectedSelector(state),
    currentListParams: currentListParamsSelector(state),
    fields:            fieldsSelector(state),
    currentViewMode:   currentViewModeSelector(state)
  })
)
export class ListContainer extends Component {
  render = () => <List {...this.props} />;
}
