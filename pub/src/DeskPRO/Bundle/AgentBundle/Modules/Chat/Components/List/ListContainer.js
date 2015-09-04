import React from 'react';
import { connect } from 'redux/react';
import { List } from './List';
import * as actions from '../../Actions/chatListActions';

@connect(state => state.ChatList)
export class ListContainer extends React.Component {

  render() {
    return (
      <List
        elements={this.props.chats}
        view={this.props.view}
        toggleView={this.toggleView.bind(this)}
        order={this.props.order}
        toggleOrder={this.toggleOrder.bind(this)}
        sort={this.sort.bind(this)}
      />
    );
  }

  toggleView(e) {
    e.preventDefault();
    this.props.dispatch(actions.toggleView());
  }

  toggleOrder(e) {
    e.preventDefault();
    this.props.dispatch(actions.toggleOrder());
  }

  sort(sort) {
    return (e) => {
      e.preventDefault();
      this.props.dispatch(actions.sort(sort));
    }
  }
}
