import React, { Component, PropTypes } from 'react';
import { connect } from 'react-redux';
import { paginationSelector, viewModeSelector } from '../../Selectors/list';
import { List } from './List';
import { toggleSelectedAction } from '../../../Application/Actions/massActions';
import { applyParams } from '../../Actions/chatListActions';
import { isLoadedCollectionSelectorFactory, setCollection, releaseCollection }
  from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore';

@connect(state => ({
  isLoaded:   isLoadedCollectionSelectorFactory('UserChat', 'chats')(state)
              && isLoadedCollectionSelectorFactory('Department', 'all_chat')(state),
  pagination: paginationSelector(state),
  viewMode:   viewModeSelector(state)
}))
export class ListContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired
  };

  componentDidMount() {
    this.props.dispatch(setCollection('UserChat', 'chats', []));
    this.props.dispatch(setCollection('Department', 'all_chat', []));
  }

  componentWillUnmount() {
    this.props.dispatch(releaseCollection('UserChat', 'chats'));
    this.props.dispatch(releaseCollection('Department', 'all_chat'));
  }

  render() {
    const toggleSelected  = id => () => this.props.dispatch(toggleSelectedAction(id));
    const handlePageClick = page => this.props.dispatch(applyParams({ page }));

    return (
      <List {...this.props} toggleSelected={toggleSelected} handlePageClick={handlePageClick} />
    );
  }
}
