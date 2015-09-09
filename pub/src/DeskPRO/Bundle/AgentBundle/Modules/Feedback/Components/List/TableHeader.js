import React from 'react';
import $ from "jquery";
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants'

export class TableHeader extends React.Component {

  render() {
    const {sortTable} = this.props;
    return (
      <thead>
      <tr>
        <th className="id-col sortable" onClick={this.handleClick.bind(this, 'id', sortTable)}>
          ID
        </th>
        <th className="sortable" onClick={this.handleClick.bind(this, 'num_ratings', sortTable)}>
          Votes
        </th>
        <th className="subject-col sortable" onClick={this.handleClick.bind(this, 'title', sortTable)}>
          Title
        </th>
        <th className="sortable" onClick={this.handleClick.bind(this, 'status', sortTable)}>
          Status
        </th>
        <th className="sortable" onClick={this.handleClick.bind(this, 'category', sortTable)}>
          Type
        </th>
        <th>Labels</th>
        <th className="user-col sortable" onClick={this.handleClick.bind(this, 'author_name', sortTable)}>
          Submitter
        </th>
      </tr>
      </thead>
    );
  }

  handleClick(param, sortTable, event) {
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
    sortTable(param, order);
  }
}