import React, {Component, PropTypes} from 'react';
import { connect } from 'react-redux';
import { DropdownMenu, Option, DropdownMenuFooter } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/GlobalWidgets/DropdownMenu';
import { OrderSwitcher } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/OrderSwitcher';
import { changeSort, toggleOrder } from '../../../Actions/chatListActions';
import { currentSortOptionSelector } from '../../../Selectors/list';

@connect(state => ({
  sortOptions: state.Chat.list.get('sortOptions').toJS(),
  order: state.Chat.list.get('order'),
  currentSortMode: currentSortOptionSelector(state)
}))
export class OrderByDropdownContainer extends Component {

  static propTypes = {
    offset: PropTypes.object.isRequired
  };

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
    const {dispatch} = this.props;
    dispatch(changeSort(option.field));
  }

  toggleListOrder(order) {
    const {dispatch} = this.props;
    dispatch(toggleOrder(order));
  }
}