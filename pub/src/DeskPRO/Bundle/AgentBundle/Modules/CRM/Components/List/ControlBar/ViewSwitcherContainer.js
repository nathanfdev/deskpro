import React from 'react';
import { connect } from 'redux/react';
import { ViewModeSwitcher } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';
import { changeDisplayFieldsStatus } from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/Actions/crmNavActions';

@connect(state => ({
  viewMode:      state.FeedbackList.viewMode,
  tableViewFields: state.FeedbackList.tableViewFields,
  listViewFields: state.FeedbackList.listViewFields
}))
export class ViewSwitcherContainer extends React.Component {
  render() {
    return (
      <ViewModeSwitcher {...this.props}  displayFieldsStatus={this.displayFieldsStatus.bind(this)} />
    );
  }

  displayFieldsStatus(type, field, status) {
    const {dispatch} = this.props;
    dispatch(changeDisplayFieldsStatus(type, field, status));
  }

}