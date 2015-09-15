import React from 'react';
import { connect } from 'redux/react';
import { ViewModeSwitcher } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';
import { changeDisplayFieldsStatus } from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/Actions/crmNavActions';

@connect(state => ({
  viewMode: state.CrmNav.viewMode,
  viewModeOptions: state.CrmNav.viewModeOptions,
  tableViewFields: state.CrmNav.tableViewFields,
  listViewFields: state.CrmNav.listViewFields
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

  displayFieldsStatus(type, field, status) {
    const {dispatch} = this.props;
    dispatch(changeDisplayFieldsStatus(type, field, status));
  }


  toggleView(newView) {
    const {dispatch} = this.props;
    dispatch(actions.toggleViewMode(newView.field));
  }


}