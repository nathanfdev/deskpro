import React from 'react';
import { connect } from 'react-redux';
import { OrderBy } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';
import { toggleOrder } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Actions/AppActions';
import { toggleSort } from 'DeskPRO/Bundle/AgentBundle/Modules/Chat/Actions/chatListActions';

@connect(state => ({
  sort:        state.ChatList.sort,
  sortName:    state.ChatList.sortName,
  sortOptions: state.ChatList.sortOptions,
  order:       state.ChatList.order
}))
export class OrderByContainer extends React.Component {

  render() {
    const { sort, sortName, sortOptions, order } = this.props;

    return (
      <OrderBy
        sort={sort}
        sortName={sortName}
        sortOptions={sortOptions}
        order={order}
        toggleSort={this.toggleListSort.bind(this)}
        toggleOrder={this.toggleListOrder.bind(this)}
      />
    );
  }

  toggleListOrder() {
    this.props.dispatch(toggleOrder());
  }

  toggleListSort(sort) {
    return (e) => {
      e.preventDefault();
      this.props.dispatch(actions.toggleSort(sort));
    }
  }

}
