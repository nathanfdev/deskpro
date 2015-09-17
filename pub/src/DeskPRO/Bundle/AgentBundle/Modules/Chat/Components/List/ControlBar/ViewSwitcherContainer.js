import React from 'react';
import { connect } from 'redux/react';
import { ViewModeSwitcher } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';

@connect(state => ({
  viewMode:      state.ChatList.viewMode,
  tableViewFields: state.ChatList.tableViewFields,
  listViewFields: state.ChatList.listViewFields
}))
export class ViewSwitcherContainer extends React.Component {
  render() {
    return (
      <ViewModeSwitcher {...this.props} />
    );
  }
}
