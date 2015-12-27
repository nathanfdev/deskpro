import React, { Component } from 'react';
import { connect } from 'react-redux';
import { List } from './List';
import { currentViewModeSelector } from '../../Selectors/list';

@connect(state => {
  return ({
    content: state.Publish.list.get('currentListParams').get('content'),
    loaded: state.Publish.list.getIn(['async', 'done']),
    pagination: state.Publish.list.get('pagination'),
    selected: state.Publish.list.get('selected'),
    currentViewMode: currentViewModeSelector(state)
  });
})
export class ListContainer extends Component {
  render() {
    return (
      <List {...this.props} />
    );
  }
}
