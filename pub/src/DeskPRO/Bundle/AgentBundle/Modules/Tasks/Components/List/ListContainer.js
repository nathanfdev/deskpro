import React from 'react';
import { connect } from 'react-redux';
import { List } from './List';
import { currentViewModeSelector } from '../../Selectors/list';

@connect(state => ({
  tasks: state.Tasks.list.get('elements'),
  loaded: state.Tasks.list.getIn(['async', 'done']),
  currentView: currentViewModeSelector(state),
  listParams: state.Tasks.list.get('listParams')
}))
export class ListContainer extends React.Component {

  render() {
    return (
      <List {...this.props} />
    );
  }
}
