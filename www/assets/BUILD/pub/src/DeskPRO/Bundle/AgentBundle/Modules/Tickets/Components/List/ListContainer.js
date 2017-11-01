import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { connect } from 'react-redux';
import { List } from './List';
import { currentViewModeSelector, paginationSelector, listParamsSelector, fieldsSelector } from '../../Selectors/list';
import { isLoadedCollectionSelectorFactory, releaseCollection, setCollection }
  from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { selectedSelector } from '../../../Application/Selectors/massActions';
import { applyListParams } from '../../Actions/listActions';

@connect(state => ({
  isLoaded:          isLoadedCollectionSelectorFactory('Ticket', 'list')(state),
  currentListParams: listParamsSelector(state),
  selected:          selectedSelector(state),
  pagination:        paginationSelector(state),
  fieldsConfig:      fieldsSelector(state),
  viewMode:          currentViewModeSelector(state)
}))
export class ListContainer extends Component {
  static propTypes = {
    dispatch:          PropTypes.func.isRequired,
    currentListParams: PropTypes.object.isRequired,
    fieldsConfig:      PropTypes.object.isRequired,
    selected:          PropTypes.object.isRequired,
    viewMode:          PropTypes.string.isRequired,
    pagination:        PropTypes.object
  };

  componentDidMount() {
    this.props.dispatch(setCollection('Ticket', 'list', []));
  }

  componentWillUnmount() {
    this.props.dispatch(releaseCollection('Ticket', 'list'));
  }

  render() {
    const handlePageClick = page => {
      this.props.dispatch(applyListParams({ page }));
    };

    return <List {...this.props} handlePageClick={handlePageClick} />;
  }
}
