import React from 'react';
import { connect } from 'redux/react';
import { DropdownMenu, Option, DropdownMenuFooter } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/GlobalWidgets/DropdownMenu';
import { OrderSwitcher } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/OrderSwitcher';
import { toggleSort, toggleOrder } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackListActions';

@connect(state => ({
  sort: state.FeedbackList.sort,
  sortName: state.FeedbackList.sortName,
  sortOptions: state.FeedbackList.sortOptions,
  order: state.FeedbackList.order,
  filters: state.FeedbackList.filters,
  query: state.FeedbackList.query
}))
export class OrderByDropdownContainer extends React.Component {

  render() {
    const { sort, sortOptions, order } = this.props;

    return (
      <DropdownMenu dropdownClass="order-dropdown">
        {sortOptions.map((option, index)=>
            <Option key={index} active={sort === option.field} callback={this.toggleListSort.bind(this)}
                    option={option}/>
        )}
        <DropdownMenuFooter>
          <OrderSwitcher order={order} toggleOrder={toggleOrder.bind(this)}/>
        </DropdownMenuFooter>
      </DropdownMenu>
    );
  }

  /** Change sort option (Order By ...)*/
  toggleListSort(option) {
    const {dispatch, order, query, filters} = this.props;
    dispatch(toggleSort(query, option.field, option.label, order, filters));
  }

  toggleListOrder(order) {
    const {dispatch, query, sort, filters} = this.props;
    dispatch(toggleOrder(query, sort, order, filters));
  }
}