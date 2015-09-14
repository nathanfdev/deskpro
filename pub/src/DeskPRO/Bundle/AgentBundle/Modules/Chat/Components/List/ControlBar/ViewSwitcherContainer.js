import React from 'react';
import { connect } from 'react-redux';
import { ListTableViewSwitcher } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';

@connect(state => ({
  viewMode:      state.Chat.list.viewMode,
  tableViewFields: state.Chat.list.tableViewFields,
  listViewFields: state.Chat.list.listViewFields
}))
export class ViewSwitcherContainer extends React.Component {
  render() {
    return (
      <ListTableViewSwitcher {...this.props} />
    );
  }
}
