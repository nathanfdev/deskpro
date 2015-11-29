import React from 'react';
import { connect } from 'react-redux';
import { currentViewModeOptionSelector } from '../../../Selectors/list';

@connect(state => ({
  viewModeOptions: state.Chat.list.get('viewModeOptions').toJS(),
  tableViewFields: state.Chat.list.get('tableViewFields').toJS(),
  listViewFields:  state.Chat.list.get('listViewFields').toJS(),
  currentViewMode: currentViewModeOptionSelector(state)
}))
export class ViewSwitcherContainer extends React.Component {
  render() {
    return (
      <div {...this.props} />
    );
  }
}
