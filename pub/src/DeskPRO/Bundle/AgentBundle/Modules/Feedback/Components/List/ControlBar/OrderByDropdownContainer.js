import React from 'react';
import { connect } from 'react-redux';
import { DropdownMenu, Option, DropdownMenuFooter } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/GlobalWidgets/DropdownMenu';
import { OrderSwitcher } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/OrderSwitcher';
import { toggleSort, toggleOrder } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackListActions';
import { sortingDataSelector } from '../../../Selectors/list';

@connect(state => ({
  sortOptions: state.Feedback.nav.get('sortOptions'),
  order: state.Feedback.nav.get('order'),
  filters: state.Feedback.nav.get('filters'),
  query: state.Feedback.nav.get('query'),
  currentSortMode: sortingDataSelector(state)
}))
export class OrderByDropdownContainer extends React.Component {

  render() {
    const { sortOptions, order, currentSortMode } = this.props;

    return (
      <DropdownMenu dropdownClass="order-dropdown">
        {sortOptions.map((option, index)=>
            <Option key={index} active={currentSortMode.field === option.field} callback={this.toggleListSort.bind(this)}
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