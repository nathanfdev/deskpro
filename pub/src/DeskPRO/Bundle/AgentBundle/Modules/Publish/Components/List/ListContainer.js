import React, {Component, PropTypes} from 'react';
import { connect } from 'react-redux';
import { List } from './List';
import * as actions from '../../Actions/publishListActions';
import { currentViewModeSelector, contentSelector, isLoadedSelector, paginationSelector } from '../../Selectors/list';
import { selectedSelector } from '../../../Application/Selectors/massActions';

@connect(state => {
  return ({
    content: contentSelector(state),
    isLoaded: isLoadedSelector(state),
    pagination: paginationSelector(state),
    selected: selectedSelector(state),
    currentViewMode: currentViewModeSelector(state)
  });
})
export class ListContainer extends Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired
  };

  toggleView(e) {
    e.preventDefault();
    this.props.dispatch(actions.toggleView());
  }

  render() {
    return (
      <List {...this.props} toggleView={this.toggleView.bind(this)} />
    );
  }
}
