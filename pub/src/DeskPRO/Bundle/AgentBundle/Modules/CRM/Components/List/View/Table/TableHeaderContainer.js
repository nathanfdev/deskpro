import React from 'react';
import $ from "jquery";
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants'
import { setTableSort } from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/Actions/crmNavActions';
import { TableHeader } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';

import { connect } from 'react-redux';
@connect(state => ({
  order: state.FeedbackList.order,
  query: state.FeedbackList.query
}))
export class TableHeaderContainer extends React.Component {

  render() {
    return (
      <TableHeader>
        <th className="id-col sortable" onClick={this.handleClick.bind(this, 'id')}>
          ID
        </th>
      </TableHeader>
    );
  }

  handleClick(param, event) {
    event.preventDefault();
    event.stopPropagation();
    let elem = $(event.target),
      siblings = elem.siblings('th'),
      caret = elem.find('i.fa');
    let order = elem.data('order') === constants.ORDER_DESC ? constants.ORDER_ASC : constants.ORDER_DESC;
    siblings.find('span.sort-direction').remove();
    siblings.data('order', '');
    elem.data('order', order);
    if (caret.length > 0) {
      caret.toggleClass('fa-caret-down').toggleClass('fa-caret-up');
    }
    else {
      elem.append('<span class="sort-direction"><i class="fa fa-caret-down"></i></span>')
    }
    const {dispatch, query, filters} = this.props;
    dispatch(setTableSort(query, param, order, filters));
  }

}