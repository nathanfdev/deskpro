import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { connect } from 'react-redux';
import {
  paginationSelector, viewModeSelector, elementsSelector, cardFieldsSelector, tableFieldsSelector,
  isLoadedSelector, currentListParamsSelector
} from '../../Selectors/list';
import { List } from './List';
import { toggleSelectedAction } from '../../../Application/Actions/massActions';
import { applyParams } from '../../Actions/listActions';
import {
  setCollection, releaseCollection
}
  from '../../../../../AppBundle/Modules/RecordsStore';

@connect(
  state => ({
    isLoaded:          isLoadedSelector(state),
    pagination:        paginationSelector(state),
    viewMode:          viewModeSelector(state),
    elements:          elementsSelector(state),
    currentListParams: currentListParamsSelector(state),
    cardFields:        cardFieldsSelector(state),
    tableFields:       tableFieldsSelector(state)
  }),
  {
    setCollection,
    releaseCollection,
    toggleSelectedAction,
    applyParams
  })

export class ListContainer extends Component {
  static propTypes = {
    setCollection:        PropTypes.func.isRequired,
    releaseCollection:    PropTypes.func.isRequired,
    toggleSelectedAction: PropTypes.func.isRequired,
    applyParams:          PropTypes.func.isRequired,
    cardFields:           PropTypes.object.isRequired,
    tableFields:          PropTypes.object.isRequired
  };

  componentDidMount() {
    this.props.setCollection('UserChat', 'chats', []);
    this.props.setCollection('Department', 'all_chat', []);
  }

  componentWillUnmount() {
    this.props.releaseCollection('UserChat', 'chats');
    this.props.releaseCollection('Department', 'all_chat');
  }

  toggleSelected = id => () => this.props.toggleSelectedAction(id);

  handlePageClick = page => this.props.applyParams({ page });

  render() {
    return (
      <List {...this.props} toggleSelected={this.toggleSelected} handlePageClick={this.handlePageClick} />
    );
  }
}
