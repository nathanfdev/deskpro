import React, { Component, PropTypes } from 'react';
import { List } from './List';
import { selectedSelector } from '../../../Application/Selectors/massActions';
import { isCommentsSelector, currentViewModeSelector, paginationSelector, isLoadedSelector, cardFieldsSelector,
  tableFieldsSelector, idsSelector } from '../../Selectors/list';
import { toggleSelectedAction } from '../../../Application/Actions/massActions';
import { applyParams } from '../../Actions/FeedbackListActions';
import { connect } from 'react-redux';

@connect(state => ({
  isComments:      isCommentsSelector(state),
  selected:        selectedSelector(state),
  pagination:      paginationSelector(state),
  isLoaded:        isLoadedSelector(state),
  currentApp:      state.Application.dpWindow.get('activeAppId'),
  currentViewMode: currentViewModeSelector(state),
  cardFields:      cardFieldsSelector(state),
  tableFields:     tableFieldsSelector(state),
  elements:        idsSelector(state)
}), {
  applyParams,
  toggleSelectedAction
})

export class ListContainer extends Component {

  static propTypes = {
    dispatch:             PropTypes.func.isRequired,
    currentApp:           PropTypes.string.isRequired,
    isComments:           PropTypes.bool,
    isLoaded:             PropTypes.bool.isRequired,
    currentViewMode:      PropTypes.string.isRequired,
    cardFields:           PropTypes.object.isRequired,
    tableFields:          PropTypes.object.isRequired,
    elements:             PropTypes.object.isRequired,
    toggleSelectedAction: PropTypes.func.isRequired,
    applyParams:          PropTypes.func.isRequired
  };

  render() {
    const toggleSelected = id => () => this.props.toggleSelectedAction(id);
    const handlePageClick = page => this.props.applyParams({ page });

    return (
      <List {...this.props} toggleSelected={toggleSelected} handlePageClick={handlePageClick} />
    );
  }
}
