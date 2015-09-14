import React from 'react';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants'
import { setTableSort } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackListActions';
import { TableHeader, Th } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';

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
        {filtered.map((field, index) => <Th key={index} field={field} sortTable={this.sortTable.bind(this)}/>
        )}
      </TableHeader>
    );
  }

  sortTable(param, order) {
    const {dispatch, query, filters} = this.props;
    dispatch(setTableSort(query, param, order, filters));
  }
}