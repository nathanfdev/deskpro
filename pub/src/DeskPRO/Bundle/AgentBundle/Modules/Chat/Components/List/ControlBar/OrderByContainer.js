import React from 'react';
import { SortingMenu }
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ControlBar/Sorting/SortingMenu';
import { currentSortOptionSelector } from '../../../Selectors/list';

import { connect } from 'react-redux';
@connect(state => ({
  sortOptions: state.Chat.list.get('sortOptions').toJS(),
  order: state.Chat.list.get('order'),
  currentSortOption: currentSortOptionSelector(state)
}))
export class OrderByContainer extends React.Component {

  render() {
    return (
      <SortingMenu {...this.props} />
    );
  }
}
