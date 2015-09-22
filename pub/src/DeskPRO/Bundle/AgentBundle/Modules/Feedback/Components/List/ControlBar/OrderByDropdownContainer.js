import React from 'react';
import { connect } from 'react-redux';
import { DropdownMenu, Option, DropdownMenuFooter } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/GlobalWidgets/DropdownMenu';
import { OrderSwitcher } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/OrderSwitcher';
import { toggleSort, toggleOrder } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackListActions';
import { sortingDataSelector } from '../../../Selectors/list';

@connect(state => ({
  sortOptions: state.Feedback.list.get('sortOptions').toJS(),
  order: state.Feedback.list.get('order'),
  filters: state.Feedback.list.get('filters'),
  query: state.Feedback.nav.get('query'),
  currentSortMode: sortingDataSelector(state)
}))
export class OrderByDropdownContainer extends React.Component {

  render() {
    const { sortOptions, order, currentSortMode, offset } = this.props;

    return (
      <DropdownMenu offset={offset}>
        {sortOptions.map((option, index)=>
            <Option key={index} active={currentSortMode.field === option.field}
                    callback={this.toggleListSort.bind(this)}
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
    const {dispatch} = this.props;
    dispatch(toggleOrder(order));
  }
}