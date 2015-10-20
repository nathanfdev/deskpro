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
    sortOptions: PropTypes.object.isRequired,
    dispatch: PropTypes.func.isRequired,
    toggleDropdown: PropTypes.func.isRequired,
    isComments: PropTypes.bool.isRequired
  },

  mixins: [
    require('react-onclickoutside')
  ],

  handleClickOutside: function handleClickOutside() {
    this.props.toggleDropdown();
  },

  toggleListOrder: function toggleListOrder(order) {
    const { dispatch, isComments, toggleDropdown } = this.props;
    if (isComments) {
      dispatch(commentsToggleOrder(order));
    } else {
      dispatch(toggleOrder(order));
    }
    toggleDropdown();
  },

  /* Change sort option (Order By ...)*/
  toggleListSort: function toggleListSort(option) {
    console.log(option);
    const { dispatch, isComments, toggleDropdown } = this.props;
    if (!isComments) {
      dispatch(toggleSort(option.field));
    }
    toggleDropdown();
  },

  renderOptions: function renderOptions() {
    const { isComments, toggleDropdown, currentSortMode, sortOptions } = this.props;
    if (isComments) {
      return (
        <Item
          isActive
          toggleDropdown={toggleDropdown}
          option={currentSortMode}
        />
      );
    }
    return (
      sortOptions.map((option, index)=>
          <Item
            key={index}
            label={option.label}
            isActive={currentSortMode.field === option.field}
            checked={currentSortMode.field === option.field}
            onClick={this.toggleListSort.bind(this, option)}
            icon={option.icon}
          />
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
