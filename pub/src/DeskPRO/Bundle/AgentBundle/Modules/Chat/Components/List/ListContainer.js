import React from 'react';
import { connect } from 'redux/react';
import { List } from './List';
import * as actions from '../../Actions/chatListActions';
import * as AppActions from "DeskPRO/Bundle/AgentBundle/Modules/Application/Actions/AppActions";
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants'
import $ from "jquery";

@connect(state => state.ChatList)
export class ListContainer extends React.Component {

  render() {

    const displayFields = [];

    return (
      <List
        {...this.props}
        displayFields={displayFields}
        toggleView={this.toggleView.bind(this)}
        toggleOrder={this.toggleOrder.bind(this)}
        toggleSort={this.toggleSort.bind(this)}
        />
    );
  }

  toggleView(e) {
    //e.preventDefault();
    this.props.dispatch(AppActions.toggleViewMode());
  }

  toggleOrder(e) {
    e.preventDefault();
    $('div.dropdown-choice').hide();
    const {dispatch, sort, order, query} = this.props;
    let elem = $(event.target),
      filters = {query, sort: sort, order: order};
    if (order === constants.ORDER_ASC) {
      elem.closest('a.ticket-control-button').find('i.fa').removeClass('fa-caret-up').addClass('fa-caret-down');
    }
    else {
      elem.closest('a.ticket-control-button').find('i.fa').removeClass('fa-caret-down').addClass('fa-caret-up');
    }
    dispatch(AppActions.toggleOrder());
    dispatch(actions.load(filters));
  }

  toggleSort(sort) {
    return (e) => {
      e.preventDefault();
      this.props.dispatch(actions.toggleSort(sort));
    }
  }

}
