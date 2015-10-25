import React, {Component, PropTypes} from 'react';
import { connect } from 'react-redux';
import { DropdownMenu, Option, DropdownMenuFooter } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/GlobalWidgets/DropdownMenu';
import { OrderSwitcher } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/OrderSwitcher';
import { changeSort, toggleOrder } from '../../../Actions/chatListActions';
import { currentSortOptionSelector } from '../../../Selectors/list';

@connect(state => ({
  sortOptions: state.Chat.list.get('sortOptions').toJS(),
  order: state.Chat.list.get('order'),
  currentSortOption: currentSortOptionSelector(state)
}))
export class OrderByDropdownContainer extends Component {

  static propTypes = {
    offset: PropTypes.object.isRequired
  };

  render() {
    const { sortOptions, order, currentSortOption, offset } = this.props;

    return (
      <DropdownMenu offset={offset}>
        {sortOptions.map((option, index)=>
            <Option key={index} active={currentSortOption.field === option.field}
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