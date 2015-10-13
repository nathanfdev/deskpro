import React, {PropTypes} from 'react';
import Menu from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import Item from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Item';
import MenuFooter from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/MenuFooter';
import MenuFooterOptions from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/MenuFooterOptions';
import { toggleSort, toggleOrder } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackListActions';
import { commentsToggleOrder } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackCommentsActions';

const OrderByDropdown = React.createClass({

  propTypes: {
    order: PropTypes.string.isRequired,
    currentSortMode: PropTypes.object.isRequired,
    currentGroup: PropTypes.object.isRequired,
    sortOptions: PropTypes.object.isRequired,
    dispatch: PropTypes.func.isRequired,
    toggleDropdown: PropTypes.func.isRequired
  },

  mixins: [
    require('react-onclickoutside')
  ],

  handleClickOutside: function handleClickOutside() {
    this.props.toggleDropdown();
  },

  toggleListOrder: function toggleListOrder(order) {
    const {dispatch, currentGroup} = this.props;
    if (currentGroup.name !== 'feedback_comments') {
      dispatch(toggleOrder(order));
    } else {
      dispatch(commentsToggleOrder(order));
    }
  },

  /* Change sort option (Order By ...)*/
  toggleListSort: function toggleListSort(option) {
    const {dispatch, currentGroup} = this.props;
    if (currentGroup.name !== 'feedback_comments') {
      dispatch(toggleSort(option.field));
    }
  },

  renderOptions: function renderOptions() {
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
  },

  render: function render() {
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

});

module.exports = OrderByDropdown;
