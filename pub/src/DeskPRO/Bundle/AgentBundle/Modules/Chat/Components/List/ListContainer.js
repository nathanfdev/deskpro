import React, {Component, PropTypes} from 'react';
import { connect } from 'react-redux';
import { paginationSelector, viewModeSelector, isLoadedSelector } from '../../Selectors/list';
import { List } from './List';
import { toggleSelectedAction } from '../../../Application/Actions/massActions';

@connect(state => ({
  isLoaded: isLoadedSelector(state),
  pagination: paginationSelector(state),
  viewMode: viewModeSelector(state)
}))
export class ListContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    isComments: PropTypes.bool,
    isLoaded: PropTypes.bool.isRequired,
    currentViewMode: PropTypes.string.isRequired
  };

  render() {
    const toggleSelected = (id) => () => this.props.dispatch(toggleSelectedAction(id));
    return (
      <List {...this.props} toggleSelected={toggleSelected}/>
    );
  }
}
