import React, {Component, PropTypes} from 'react';
import { connect } from 'react-redux';
import { Option, DropdownMenuFooter } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/GlobalWidgets/DropdownMenu';
import Menu from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/GlobalWidgets/Menu';
import { OrderSwitcher } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/OrderSwitcher';
import { toggleSort, toggleOrder } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackListActions';
import { sortingDataSelector } from '../../../Selectors/list';

@connect(state => ({
  sortOptions: state.Feedback.list.get('sortOptions').toJS(),
  order: state.Feedback.list.get('order'),
  currentSortMode: sortingDataSelector(state)
}))
export class OrderByDropdownContainer extends Component {

  static propTypes = {
    offset: PropTypes.object.isRequired
  };

  render() {
    const { sortOptions, order, currentSortMode, offset, toggleDropdown } = this.props;

    return (
      <Menu offset={offset} toggleDropdown={toggleDropdown}>
        {sortOptions.map((option, index)=>
            <Option
              key={index}
              active={currentSortMode.field === option.field}
              callback={this.toggleListSort.bind(this)}
              toggleDropdown={toggleDropdown}
              option={option}
              />
        )}
        <DropdownMenuFooter>
          <OrderSwitcher
            order={order}
            toggleOrder={this.toggleListOrder.bind(this)}
            toggleDropdown={this.props.toggleDropdown}
            />
        </DropdownMenuFooter>
      </Menu>
    );
  }

  /** Change sort option (Order By ...)*/
  toggleListSort(option) {
    const {dispatch} = this.props;
    dispatch(toggleSort(option.field));
  }

  toggleListOrder(order) {
    const {dispatch} = this.props;
    dispatch(toggleOrder(order));
  }
}