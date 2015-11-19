import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { List } from './List';
import { currentViewModeSelector, elementsSelector } from '../../Selectors/list';
import { unload } from '../../Actions/listActions';

@connect(state => ({
  tasks: elementsSelector(state),
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
