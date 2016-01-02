import React, { Component } from 'react';
import { List } from './List';
import { currentViewModeSelector, currentContentSelector } from '../../Selectors/list';

import { connect } from 'react-redux';
@connect(state => {
  return ({
    loaded: state.CRM.list.getIn(['async', 'done']),
    pagination: state.CRM.list.get('pagination'),
    selected: state.CRM.list.get('selected'),
    currentViewMode: currentViewModeSelector(state),
    content: currentContentSelector(state)
  });
})
export class ListContainer extends Component {
  render() {
    return (
      <List {...this.props} />
    );
  }
}
