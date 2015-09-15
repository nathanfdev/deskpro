import React from 'react';
import { connect } from 'react-redux';
import { ListTableViewSwitcher } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';

@connect(state => ({
  viewMode:      state.Chat.list.get('viewMode'),
  tableViewFields: state.Chat.list.get('tableViewFields'),
  listViewFields: state.Chat.list.get('listViewFields')
}))
export class ViewSwitcherContainer extends React.Component {
  render() {
    return (
      <ListTableViewSwitcher {...this.props} />
    );
  }
}
