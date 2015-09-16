import React from 'react';
import { connect } from 'redux/react';
import { ViewModeSwitcher } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';
import { changeDisplayFieldsStatus } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackListActions';
import * as actions from "DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackListActions";

@connect(state => ({
  sort: state.FeedbackList.sort,
  order: state.FeedbackList.order,
  filters: state.FeedbackList.filters,
  viewModeOptions: state.FeedbackList.viewModeOptions,
  tableViewFields: state.FeedbackList.tableViewFields,
  listViewFields: state.FeedbackList.listViewFields
}))
export class ViewSwitcherContainer extends React.Component {
  render() {
    return (
      <ViewModeSwitcher
        {...this.props}
        toggleView={this.toggleView.bind(this)}
        displayFieldsStatus={this.displayFieldsStatus.bind(this)}/>
    );
  }

  toggleView(newView) {
    const {dispatch} = this.props;
    dispatch(actions.toggleViewMode(newView.field));
  }

  displayFieldsStatus(type, field, status) {
    const {dispatch, query, sort, order, filters, tableViewFields, listViewFields } = this.props;
    dispatch(changeDisplayFieldsStatus(type, field, status, query, sort, order, filters, tableViewFields, listViewFields));
  }

}