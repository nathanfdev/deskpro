import React from 'react';
import { connect } from 'redux/react';
import { ListTableViewSwitcher } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';

@connect(state => ({
  viewMode:      state.ChatList.viewMode,
  displayFields: state.ChatList.displayFields,
}))
export class ViewSwitcherContainer extends React.Component {
  render() {
    return (
      <ListTableViewSwitcher {...this.props} />
    );
  }
}
