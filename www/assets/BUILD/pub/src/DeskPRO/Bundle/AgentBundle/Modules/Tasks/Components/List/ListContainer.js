import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import { List } from './List';
import { selectedSelector } from '../../../Application/Selectors/massActions';
import { currentViewModeSelector, listParamsNavSelector, isLoadedSelector } from '../../Selectors/list';
import { unload } from '../../Actions/listActions';
import { pureRender } from 'Ampliflux';

@connect(state => ({
  isLoaded:    isLoadedSelector(state),
  currentView: currentViewModeSelector(state),
  selected:    selectedSelector(state),
  currentNav:  listParamsNavSelector(state)
}))

@pureRender

export class ListContainer extends React.Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired
  };

  render() {
    return <List {...this.props} />;
  }
}
