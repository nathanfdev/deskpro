import React from 'react';
import { connect } from 'react-redux';
import { OrderBy } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';
import { toggleSort, toggleOrder } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackListActions';
import { sortingDataSelector } from '../../../Selectors/list';

@connect(state => ({
  sortOptions: state.Feedback.nav.get('sortOptions').toJS(),
  order: state.Feedback.nav.get('order'),
  currentSortMode: sortingDataSelector(state)
}))
export class OrderByContainer extends React.Component {

  render() {
    const { sortOptions, order, currentSortMode } = this.props;
    return (
      <OrderBy
        sortOptions={sortOptions}
        currentSortMode={currentSortMode}
        order={order}
        />
    );
  }


}