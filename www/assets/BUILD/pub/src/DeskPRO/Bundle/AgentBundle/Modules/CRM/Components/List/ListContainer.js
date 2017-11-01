import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { List } from './List';
import {
  currentListParamsSelector,
  currentViewModeSelector,
  currentContentSelector,
  isLoadedSelector,
  paginationSelector,
  orgFieldsSelector,
  peopleFieldsSelector
} from '../../Selectors/list';
import { selectedSelector } from '../../../Application/Selectors/massActions';
import { applyParams } from '../../Actions/crmListActions';
import { connect } from 'react-redux';

@connect(
  state => ({
    isLoaded:          isLoadedSelector(state),
    pagination:        paginationSelector(state),
    selected:          selectedSelector(state),
    currentListParams: currentListParamsSelector(state),
    currentViewMode:   currentViewModeSelector(state),
    content:           currentContentSelector(state),
    peopleFields:      peopleFieldsSelector(state),
    orgFields:         orgFieldsSelector(state)
  }),
  { applyParams })

export class ListContainer extends Component {

  static propTypes = {
    selected:        PropTypes.object.isRequired,
    currentViewMode: PropTypes.string.isRequired,
    content:         PropTypes.string.isRequired,
    isLoaded:        PropTypes.bool,
    pagination:      PropTypes.object,
    applyParams:     PropTypes.func.isRequired,
    peopleFields:    PropTypes.object.isRequired,
    orgFields:       PropTypes.object.isRequired
  };

  handlePageClick = page => this.props.applyParams({ page });

  render() {
    return (
      <List {...this.props} handlePageClick={this.handlePageClick} />
    );
  }
}
