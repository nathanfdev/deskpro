import React, {Component, PropTypes} from 'react';
import { connect } from 'react-redux';
import { OrderBy } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import { OrderByDropdown } from './OrderByDropdown';
import { toggleSort, toggleOrder } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackListActions';
import { commentsToggleOrder } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackCommentsActions';
import { sortingDataSelector } from '../../../Selectors/list';
import { groupDataSelector } from '../../../Selectors/nav';

@connect(state => ({
  sortOptions: state.Feedback.list.get('sortOptions').toJS(),
  order: state.Feedback.list.get('order'),
  currentSortMode: sortingDataSelector(state),
  currentGroup: groupDataSelector(state)
}))

export class OrderByContainer extends Component {

  static propTypes = {
    order: PropTypes.string.isRequired,
    expanded: PropTypes.bool.isRequired,
    offset: PropTypes.object.isRequired,
    currentSortMode: PropTypes.object.isRequired,
    sortOptions: PropTypes.object.isRequired,
    currentGroup: PropTypes.object.isRequired,
    toggleDropdown: PropTypes.func.isRequired,
    dispatch: PropTypes.func.isRequired
  };

  /* Change sort option (Order By ...)*/
  toggleListSort(option) {
    const {dispatch, currentGroup} = this.props;
    if (currentGroup.name !== 'feedback_comments') {
      dispatch(toggleSort(option.field));
    }
  }

  toggleListOrder(order) {
    const {dispatch, currentGroup} = this.props;
    if (currentGroup.name !== 'feedback_comments') {
      dispatch(toggleOrder(order));
    } else {
      dispatch(commentsToggleOrder(order));
    }
  }

  renderDropdown() {
    const {expanded, offset, toggleDropdown, order, currentSortMode, sortOptions, currentGroup} = this.props;
    if (expanded) {
      return (
        <OrderByDropdown
          offset={offset}
          order={order}
          toggleListSort={this.toggleListSort.bind(this)}
          toggleListOrder={this.toggleListOrder.bind(this)}
          currentSortMode={currentSortMode}
          toggleDropdown={toggleDropdown}
          sortOptions={sortOptions}
          currentGroup={currentGroup}
          />
      );
    }
  }

  render() {
    const { sortOptions, order, currentSortMode, toggleDropdown } = this.props;
    return (
      <OrderBy
        sortOptions={sortOptions}
        currentSortMode={currentSortMode}
        order={order}
        toggleDropdown={toggleDropdown}
        >
        {this.renderDropdown()}
      </OrderBy>
    );
  }

}