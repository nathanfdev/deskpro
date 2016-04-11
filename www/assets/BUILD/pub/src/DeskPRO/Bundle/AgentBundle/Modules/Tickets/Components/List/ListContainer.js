import React, { Component, PropTypes } from 'react';
import { List } from './List';
import { viewModeSelector,paginationSelector } from '../../Selectors/list';
import { isLoadedCollectionSelectorFactory, releaseCollection, setCollection }
  from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';
import { selectedSelector } from '../../../Application/Selectors/massActions';
import { applyListParams } from '../../Actions/listActions';

import { connect } from 'react-redux';
@connect(state => ({
  isLoaded:   isLoadedCollectionSelectorFactory('Ticket', 'list')(state),
  selected:   selectedSelector(state),
  pagination: paginationSelector(state),
  viewMode:   viewModeSelector(state)
}))
export class ListContainer extends Component {
  static propTypes = {
    dispatch:   PropTypes.func.isRequired,
    selected:   PropTypes.object.isRequired,
    pagination: PropTypes.object
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
