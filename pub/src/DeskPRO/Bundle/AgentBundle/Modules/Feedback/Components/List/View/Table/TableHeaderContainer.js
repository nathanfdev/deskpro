import React from 'react';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants'
import { setTableSort } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackListActions';
import { TableHeader, Th } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';

import { connect } from 'react-redux';
@connect(state => ({
  feedback: state.FeedbackList.feedback,
  order: state.FeedbackList.order,
  filters: state.FeedbackList.filters,
  query: state.FeedbackList.query,
  tableViewFields: state.FeedbackList.tableViewFields
}))

export class TableHeaderContainer extends React.Component {

  render() {
    const { tableViewFields } = this.props;
    return (
      <TableHeader tableViewFields={tableViewFields} sortTable={this.sortTable.bind(this)}/>
    );
  }

  sortTable(param, order) {
    const {dispatch, query, filters} = this.props;
    dispatch(setTableSort(query, param, order, filters));
  }
}