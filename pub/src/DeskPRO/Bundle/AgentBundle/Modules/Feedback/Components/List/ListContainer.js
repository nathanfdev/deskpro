import React, {Component, PropTypes} from 'react';
import { List } from './List';
import { isCommentsSelector, currentViewModeSelector, selectedSelector, paginationSelector, loadedSelector }
  from '../../Selectors/list';
import { toggleSelectedAction } from '../../Actions/FeedbackMassActions';

import { connect } from 'react-redux';
@connect(state => {
  return ({
    isComments: isCommentsSelector(state),
    selected: selectedSelector(state),
    pagination: paginationSelector(state),
    loaded: loadedSelector(state),
    currentApp: state.Application.dpWindow.get('activeAppId'),
    currentViewMode: currentViewModeSelector(state)
  });
})
export class ListContainer extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    currentApp: PropTypes.string.isRequired,
    isComments: PropTypes.bool,
    loaded: PropTypes.bool.isRequired,
    currentViewMode: PropTypes.string.isRequired
  };

  render() {
    const toggleSelected = (id) => () => this.props.dispatch(toggleSelectedAction(id));

    return (
      <List {...this.props} toggleSelected={toggleSelected}/>
    );
  }
}
