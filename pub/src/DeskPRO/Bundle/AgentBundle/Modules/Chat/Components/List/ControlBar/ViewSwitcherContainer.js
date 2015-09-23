import React from 'react';
import { connect } from 'react-redux';
import { ViewModeSwitcher } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';
import { viewDataSelector } from '../../../Selectors/list';

@connect(state => ({
  viewModeOptions: state.Chat.list.get('viewModeOptions').toJS(),
  tableViewFields: state.Chat.list.get('tableViewFields').toJS(),
  listViewFields:  state.Chat.list.get('listViewFields').toJS(),
  currentViewMode: viewDataSelector(state)
}))
export class ViewSwitcherContainer extends React.Component {
  render() {
    return (
      <ViewModeSwitcher {...this.props} />
    );
  }
}
