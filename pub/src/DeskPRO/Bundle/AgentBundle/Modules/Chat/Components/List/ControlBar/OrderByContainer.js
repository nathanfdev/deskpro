import React from 'react';
import { connect } from 'react-redux';
import { OrderBy } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';
import { toggleOrder } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Actions/AppActions';
import { toggleSort } from 'DeskPRO/Bundle/AgentBundle/Modules/Chat/Actions/chatListActions';

@connect(state => ({
  sort:        state.Chat.list.get('sort'),
  sortName:    state.Chat.list.get('sortName'),
  sortOptions: state.Chat.list.get('sortOptions'),
  order:       state.Chat.list.get('order')
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
