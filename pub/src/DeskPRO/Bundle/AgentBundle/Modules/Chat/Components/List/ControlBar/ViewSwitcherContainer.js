import React from 'react';
import { connect } from 'react-redux';
import { ViewModeSwitcher } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';

@connect(state => ({
  viewMode:      state.Chat.list.get('viewMode'),
  tableViewFields: state.Chat.list.get('tableViewFields').toJS(),
  listViewFields: state.Chat.list.get('listViewFields').toJS()
}))
export class ViewSwitcherContainer extends React.Component {
  render() {
    return (
      <ViewModeSwitcher {...this.props} />
    );
  }
}
