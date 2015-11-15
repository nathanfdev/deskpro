import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { List } from './List';
import { currentViewModeSelector } from '../../Selectors/list';
import { unload } from '../../Actions/listActions';

@connect(state => ({
  tasks: state.Tasks.list.get('elements'),
  loaded: state.Tasks.list.getIn(['async', 'done']),
  currentView: currentViewModeSelector(state),
  listParams: state.Tasks.list.get('listParams')
}))
export class ListContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.object.isRequired
  };

  componentWillUnmount() {
    this.props.dispatch(unload());
  }

  render() {
    return <List {...this.props} />;
  }
}
