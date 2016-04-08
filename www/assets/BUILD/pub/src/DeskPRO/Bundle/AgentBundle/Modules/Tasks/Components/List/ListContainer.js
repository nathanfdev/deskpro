import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { List } from './List';
import { selectedSelector } from '../../../Application/Selectors/massActions';
import { currentViewModeSelector, listParamsNavSelector, isLoadedSelector }
  from '../../Selectors/list';
import { unload } from '../../Actions/listActions';

@connect(state => {
  return ({
  isLoaded: isLoadedSelector(state),
  currentView: currentViewModeSelector(state),
  selected: selectedSelector(state),
  currentNav: listParamsNavSelector(state)
})})
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
