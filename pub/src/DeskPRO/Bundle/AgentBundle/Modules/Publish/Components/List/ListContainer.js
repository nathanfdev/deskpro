import React, {Component, PropTypes} from 'react';
import { connect } from 'react-redux';
import { List } from './List';
import * as actions from '../../Actions/publishListActions';
import { currentViewModeSelector, contentSelector, loadedSelector, paginationSelector } from '../../Selectors/list';
import { selectedSelector } from '../../../Application/Selectors/massActions';

@connect(state => {
  return ({
    content: contentSelector(state),
    loaded: loadedSelector(state),
    pagination: paginationSelector(state),
    selected: selectedSelector(state),
    currentViewMode: currentViewModeSelector(state)
  });
})
export class ListContainer extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    content: PropTypes.string.isRequired,
    pagination: PropTypes.object,
    selected: PropTypes.object.isRequired,
    loaded: PropTypes.bool.isRequired,
    currentViewMode: PropTypes.string.isRequired
  };

  toggleView(e) {
    e.preventDefault();
    this.props.dispatch(actions.toggleView());
  }

  render() {
    const {content, currentViewMode, loaded, pagination, selected} = this.props;
    return (
      <List loaded={loaded}
            selected={selected}
            pagination={pagination}
            currentViewMode={currentViewMode}
            content={content}
            toggleView={this.toggleView.bind(this)}/>
    );
  }
}
