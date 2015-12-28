import React, { Component } from 'react';
import { connect } from 'react-redux';
import { List } from './List';
import { currentViewModeSelector } from '../../Selectors/list';

@connect(state => {
  return ({
    content: state.CRM.list.get('currentListParams').get('content'),
    loaded: state.CRM.list.getIn(['async', 'done']),
    pagination: state.CRM.list.get('pagination'),
    selected: state.CRM.list.get('selected'),
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
