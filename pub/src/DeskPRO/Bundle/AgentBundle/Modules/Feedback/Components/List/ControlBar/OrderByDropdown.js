import React, {PropTypes} from 'react';
import Menu from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Menu';
import Item from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/Item';
import MenuFooter from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/MenuFooter';
import MenuFooterOptions from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Menu/MenuFooterOptions';
import { setSort, setOrder } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackListActions';
import { commentsToggleOrder } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackCommentsActions';

const OrderByDropdown = React.createClass({

  propTypes: {
    order: PropTypes.string.isRequired,
    currentSortOption: PropTypes.string.isRequired,
    sortOptions: PropTypes.object.isRequired,
    dispatch: PropTypes.func.isRequired,
    toggleDropdown: PropTypes.func.isRequired,
    isComments: PropTypes.bool.isRequired
  },

  mixins: [
    require('react-onclickoutside')
  ],

  handleClickOutside() {
    this.props.toggleDropdown();
  },

  toggleListOrder(order) {
    const { dispatch, isComments, toggleDropdown } = this.props;
    if (isComments) {
      dispatch(commentsToggleOrder(order));
    } else {
      dispatch(setOrder(order));
    }
    toggleDropdown();
  },

  /* Change sort option (Order By ...)*/
  toggleListSort(option) {
    const { dispatch, isComments, toggleDropdown } = this.props;
    if (!isComments) {
      dispatch(setSort(option.field));
    }
    toggleDropdown();
  },

  renderOptions() {
    const { isComments, toggleDropdown, currentSortOption, sortOptions } = this.props;
    if (isComments) {
      return (
        <Item
          isActive
          toggleDropdown={toggleDropdown}
          option={currentSortOption}
        />
      );
    }
    return (
      sortOptions.map((option, index)=>
          <Item
            key={index}
            label={option.label}
            isActive={currentSortOption === option}
            checked={currentSortOption === option}
            onClick={this.toggleListSort.bind(this, option)}
            icon={option.icon}
          />
      )
    );
  },

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

});

module.exports = OrderByDropdown;
