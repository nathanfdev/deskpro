import React from 'react';
import { connect } from 'redux/react';
import { DropdownMenu, Option, DropdownMenuFooter } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/GlobalWidgets/DropdownMenu';
import { OrderSwitcher } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/OrderSwitcher';
import { toggleSort, toggleOrder } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackListActions';

@connect(state => ({
  sortOptions: state.FeedbackList.sortOptions,
  order: state.FeedbackList.order,
  filters: state.FeedbackList.filters,
  query: state.FeedbackList.query
}))
export class OrderByDropdownContainer extends React.Component {

  render() {
    const { sortOptions, order } = this.props;
    let currentSort = sortOptions.find((option)=>option.current === true);

    return (
      <DropdownMenu dropdownClass="order-dropdown">
        {sortOptions.map((option, index)=>
            <Option key={index} active={currentSort.field === option.field} callback={this.toggleListSort.bind(this)}
                    option={option}/>
        )}
        <DropdownMenuFooter>
          <OrderSwitcher order={order} toggleOrder={this.toggleListOrder.bind(this)}/>
        </DropdownMenuFooter>
      </DropdownMenu>
    );
  }

  /** Change sort option (Order By ...)*/
  toggleListSort(option) {
    const {dispatch, order, query, filters} = this.props;
    dispatch(toggleSort(query, option.field, order, filters));
  }

  toggleListOrder(order) {
    const {dispatch, query, sortOptions, filters} = this.props;
    let sort = sortOptions.find((option)=>option.current === true).field;
    dispatch(toggleOrder(query, sort, order, filters));
  }
}