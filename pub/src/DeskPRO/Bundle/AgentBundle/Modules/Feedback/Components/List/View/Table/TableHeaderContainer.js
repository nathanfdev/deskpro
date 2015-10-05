import React, {Component, PropTypes} from 'react';
import { setTableSort } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackListActions';
import { TableHeader } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';

import { connect } from 'react-redux';
@connect(state => ({
  tableViewFields: state.Feedback.list.get('tableViewFields').toJS()
}))

export class TableHeaderContainer extends Component {

  static propTypes = {
    tableViewFields: PropTypes.array.isRequired,
    dispatch: PropTypes.func.isRequired
  };

  sortTable(param, order) {
    const {dispatch} = this.props;
    dispatch(setTableSort(param, order));
  }

  render() {
    const { tableViewFields } = this.props;
    return (
      <TableHeader tableViewFields={tableViewFields} sortTable={this.sortTable.bind(this)}/>
    );
  }

}