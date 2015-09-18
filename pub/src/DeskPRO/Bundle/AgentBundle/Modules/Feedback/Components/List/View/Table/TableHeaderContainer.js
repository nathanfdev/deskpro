import React from 'react';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants'
import { setTableSort } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackListActions';
import { TableHeader, Th } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';

import { connect } from 'react-redux';
@connect(state => ({
  feedback: state.Feedback.nav.get('feedback'),
  order: state.Feedback.nav.get('order'),
  filters: state.Feedback.nav.get('filters'),
  query: state.Feedback.nav.get('query'),
  tableViewFields: state.Feedback.nav.get('tableViewFields')
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