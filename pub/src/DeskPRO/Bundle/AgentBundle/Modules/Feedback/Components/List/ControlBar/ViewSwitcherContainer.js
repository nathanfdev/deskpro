import React from 'react';
import { connect } from 'redux/react';
import { ListTableViewSwitcher } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';
import { changeDisplayFieldsStatus } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackListActions';

@connect(state => ({
  sort: state.FeedbackList.sort,
  order: state.FeedbackList.order,
  filters: state.FeedbackList.filters,
  viewMode: state.FeedbackList.viewMode,
  viewModeOptions: state.FeedbackList.viewModeOptions,
  tableViewFields: state.FeedbackList.tableViewFields,
  listViewFields: state.FeedbackList.listViewFields
}))
export class ViewSwitcherContainer extends React.Component {
  render() {
    return (
      <ListTableViewSwitcher {...this.props} displayFieldsStatus={this.displayFieldsStatus.bind(this)}/>
    );
  }

  displayFieldsStatus(type, field, status) {
    const {dispatch, query, sort, order, filters, tableViewFields, listViewFields } = this.props;
    dispatch(changeDisplayFieldsStatus(type, field, status, query, sort, order, filters, tableViewFields, listViewFields));
  }

}