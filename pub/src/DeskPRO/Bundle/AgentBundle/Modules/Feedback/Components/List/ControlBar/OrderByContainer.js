import React, {Component, PropTypes} from 'react';
import { OrderBy } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import { toggleSort, toggleOrder } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackListActions';
import { commentsToggleOrder } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackCommentsActions';
import { sortingDataSelector } from '../../../Selectors/list';
import { groupDataSelector } from '../../../Selectors/nav';
import Positioned from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Positioned';
import Menu from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import Item from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Item';
import MenuFooter from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/MenuFooter';
import MenuFooterOptions from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/MenuFooterOptions';

import { connect } from 'react-redux';
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

  renderOptions() {
    const {currentGroup, toggleDropdown, currentSortMode, sortOptions} = this.props;
    if (currentGroup.name === 'feedback_comments') {
      return (
        <Item
          active
          toggleDropdown={toggleDropdown}
          option={currentSortMode}
          />
      );
    }
    return (
      sortOptions.map((option, index)=>
          <Item
            key={index}
            isActive={currentSortMode.field === option.field}
            onClick={this.toggleListSort.bind(this, option)}
            icon={option.icon}
            >
            {option.label}
          </Item>
      )
    );
  }

  render() {
    const { expanded, sortOptions, order, currentSortMode, toggleDropdown } = this.props;
    return (
      <OrderBy
        sortOptions={sortOptions}
        currentSortMode={currentSortMode}
        order={order}
        toggleDropdown={toggleDropdown}
        ref="orderButton"
        >
        <Positioned isOpen={expanded}
                    positionAt="left bottom"
                    positionTarget={this.refs.orderButton}>
          <Menu>
            {this.renderOptions()}
            <MenuFooter>
              <MenuFooterOptions options={[{id: 'asc', onClick: this.toggleListOrder.bind(this, 'asc'), label: 'Asc'},
                                             {id: 'desc', onClick: this.toggleListOrder.bind(this, 'desc'), label: 'Desc'}
                                            ]} active={order}>
                Sort
              </MenuFooterOptions>
            </MenuFooter>
          </Menu>
        </Positioned>
      </OrderBy>
    );
  }

}