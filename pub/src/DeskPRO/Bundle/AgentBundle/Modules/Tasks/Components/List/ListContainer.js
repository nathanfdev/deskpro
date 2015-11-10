import React from 'react';
import { connect } from 'react-redux';
import { List } from './List';
import { currentViewModeSelector } from '../../Selectors/tasks';

@connect(state => ({
  tasks: state.Tasks.tasks.get('elements'),
  loaded: state.Tasks.tasks.getIn(['async', 'done']),
  currentView: currentViewModeSelector(state),
  listParams: state.Tasks.tasks.get('listParams')
}))
export class ListContainer extends React.Component {

  render() {
    return (
      <List {...this.props} />
    );
  }
}
