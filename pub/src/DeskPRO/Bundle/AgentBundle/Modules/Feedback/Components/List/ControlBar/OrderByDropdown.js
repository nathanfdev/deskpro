import React, {Component, PropTypes} from 'react';
import { Option, DropdownMenuFooter } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/GlobalWidgets/DropdownMenu';
import Menu from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/GlobalWidgets/Menu';
import { OrderSwitcher } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/OrderSwitcher';

export class OrderByDropdown extends Component {

  static propTypes = {
    order: PropTypes.string.isRequired,
    currentSortMode: PropTypes.object.isRequired,
    currentGroup: PropTypes.object.isRequired,
    sortOptions: PropTypes.object.isRequired,
    toggleDropdown: PropTypes.func.isRequired,
    toggleListSort: PropTypes.func.isRequired,
    toggleListOrder: PropTypes.func.isRequired,
    offset: PropTypes.object.isRequired
  };

  renderOptions() {
    const {currentGroup, toggleDropdown, currentSortMode, sortOptions, toggleListSort} = this.props;
    if (currentGroup.name === 'feedback_comments') {
      return (
        <Option
          active
          toggleDropdown={toggleDropdown}
          option={currentSortMode}
          />
      );
    }
    return (
      sortOptions.map((option, index)=>
          <Option
            key={index}
            active={currentSortMode.field === option.field}
            callback={toggleListSort.bind(this)}
            toggleDropdown={toggleDropdown}
            option={option}
            />
      )
    );
  }

  render() {
    const { order, offset, toggleDropdown, toggleListOrder } = this.props;

    return (
      <Menu offset={offset} toggleDropdown={toggleDropdown}>
        {this.renderOptions()}
        <DropdownMenuFooter>
          <OrderSwitcher
            order={order}
            toggleOrder={toggleListOrder}
            toggleDropdown={toggleDropdown}
            />
        </DropdownMenuFooter>
      </Menu>
    );
  }

}