import React, { Component, PropTypes } from 'react';
import { List } from './List';
import { connect } from 'react-redux';
import { viewModeSelector } from '../../Selectors/list';
import { isLoadedCollectionSelectorFactory, releaseCollection } from 'DeskPRO/Bundle/AgentBundle/Modules/RecordsStore';

@connect(state => ({
  isLoaded: isLoadedCollectionSelectorFactory('Ticket', 'list')(state),
  viewMode: viewModeSelector(state)
}))
export class ListContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired
  };

  componentWillUnmount() {
    this.props.dispatch(releaseCollection('Ticket', 'list'));
  }

  render() {
    return <List {...this.props} />;
  }
}
