import React, {Component, PropTypes} from 'react';
import { connect } from 'react-redux';
import { List } from './List';
import * as actions from '../../Actions/publishListActions';
import { currentViewModeSelector } from '../../Selectors/list';

@connect(state => {
  return ({
    content: state.Publish.list.get('currentListParams').get('content'),
    loaded: state.Publish.list.getIn(['async', 'done']),
    pagination: state.Publish.list.get('pagination'),
    selected: state.Publish.list.get('selected'),
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
            toggleView={this.toggleView.bind(this)}
        />
    );
  }
}
