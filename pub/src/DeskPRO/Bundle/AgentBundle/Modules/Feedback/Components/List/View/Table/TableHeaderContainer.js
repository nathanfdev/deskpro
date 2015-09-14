import React from 'react';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants'
import { setTableSort } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackListActions';
import { TableHeader } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';
import $ from "jquery";

import { connect } from 'redux/react';
@connect(state => ({
  order: state.FeedbackList.order,
  filters: state.FeedbackList.filters,
  query: state.FeedbackList.query,
  tableViewFields: state.FeedbackList.tableViewFields
}))
export class TableHeaderContainer extends React.Component {

  render() {
    const { tableViewFields } = this.props;
    let filtered = tableViewFields.filter(function (field) {
      return field.status !== constants.FIELD_HIDDEN
    });
    filtered.sort(function (a, b) {
      return a.priority - b.priority
    });

    return (
      <TableHeader>
        {filtered.map((element, index) =>
            <th key={index} className={'sortable '+ element.className}
                onClick={this.handleClick.bind(this, element.name)}>
              {element.label}
            </th>
        )}
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