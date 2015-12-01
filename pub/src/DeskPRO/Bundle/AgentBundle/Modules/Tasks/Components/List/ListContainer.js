import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { List } from './List';
import { currentViewModeSelector, elementsSelector, listParamsNavSelector, isDoneSelector } from '../../Selectors/list';
import { unload } from '../../Actions/listActions';

@connect(state => ({
  tasks: elementsSelector(state),
  loaded: isDoneSelector(state),
  currentView: currentViewModeSelector(state),
  currentNav: listParamsNavSelector(state)
}))
export class ListContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired
  };

  componentWillUnmount() {
    this.props.dispatch(unload());
  }

  render() {
    return <List {...this.props} />;
  }
}
