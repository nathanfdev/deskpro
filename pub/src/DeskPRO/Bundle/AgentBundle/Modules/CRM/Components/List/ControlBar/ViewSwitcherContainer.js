import React from 'react';
import { connect } from 'redux/react';
import { ListTableViewSwitcher } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';
import { changeDisplayFieldsStatus } from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/Actions/crmNavActions';

@connect(state => ({
  viewMode:      state.FeedbackList.viewMode,
  tableViewFields: state.FeedbackList.tableViewFields,
  listViewFields: state.FeedbackList.listViewFields
}))
export class ViewSwitcherContainer extends React.Component {
  render() {
    return (
      <ListTableViewSwitcher {...this.props}  displayFieldsStatus={this.displayFieldsStatus.bind(this)} />
    );
  }

  displayFieldsStatus(type, field, status) {
    const {dispatch} = this.props;
    dispatch(changeDisplayFieldsStatus(type, field, status));
  }

}