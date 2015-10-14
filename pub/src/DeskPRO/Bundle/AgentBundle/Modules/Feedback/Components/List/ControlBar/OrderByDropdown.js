import React, {Component, PropTypes} from 'react';
import Menu from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import Item from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Item';
import MenuFooter from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/MenuFooter';
import MenuFooterOptions from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/MenuFooterOptions';
import { OrderSwitcher } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/OrderSwitcher';
import { toggleSort, toggleOrder } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackListActions';
import { commentsToggleOrder } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackCommentsActions';

export class OrderByDropdown extends Component {

  static propTypes = {
    order: PropTypes.string.isRequired,
    currentSortMode: PropTypes.object.isRequired,
    sortOptions: PropTypes.object.isRequired,
    dispatch: PropTypes.func.isRequired,
    toggleDropdown: PropTypes.func.isRequired
  };


  toggleListOrder(order) {
    const {dispatch, currentGroup} = this.props;
    if (currentGroup.name !== 'feedback_comments') {
      dispatch(toggleOrder(order));
    } else {
      dispatch(commentsToggleOrder(order));
    }
  }

  /* Change sort option (Order By ...)*/
  toggleListSort(option) {
    const {dispatch, currentGroup} = this.props;
    if (currentGroup.name !== 'feedback_comments') {
      dispatch(toggleSort(option.field));
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
            checked={currentSortMode.field === option.field}
            onClick={this.toggleListSort.bind(this, option)}
            icon={option.icon}
            >
            {option.label}
          </Item>
      )
    );
  }

  render() {
    const { order } = this.props;

    return (
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
    );
  }

}