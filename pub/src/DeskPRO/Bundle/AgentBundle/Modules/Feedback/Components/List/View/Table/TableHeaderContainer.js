import React from 'react';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants'
import { setTableSort } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackListActions';
import { TableHeader, Th } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';

import { connect } from 'react-redux';
@connect(state => ({
  order: state.Feedback.list.get('order'),
  filters: state.Feedback.list.get('filters'),
  tableViewFields: state.Feedback.list.get('tableViewFields').toJS(),
  feedback: state.Feedback.list.get('feedback'),
  query: state.Feedback.nav.get('query')
}))

export class TableHeaderContainer extends React.Component {

  render() {
    const { tableViewFields } = this.props;
    return (
      <TableHeader tableViewFields={tableViewFields} sortTable={this.sortTable.bind(this)}/>
    );
  }

  sortTable(param, order) {
    const {dispatch} = this.props;
    dispatch(setTableSort(param, order));
  }
}