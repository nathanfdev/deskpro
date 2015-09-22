import React from 'react';
import { connect } from 'react-redux';
import { OrderBy } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';
import { toggleSort, toggleOrder } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackListActions';
import { sortingDataSelector } from '../../../Selectors/list';

@connect(state => ({
  sortOptions: state.Feedback.list.get('sortOptions').toJS(),
  order: state.Feedback.list.get('order'),
  currentSortMode: sortingDataSelector(state)
}))
export class OrderByContainer extends React.Component {

  render() {
    const { sortOptions, order, currentSortMode, toggleDropdown } = this.props;

    return (
      <OrderBy
        sortOptions={sortOptions}
        currentSortMode={currentSortMode}
        order={order}
        toggleDropdown={toggleDropdown}
        />
    );
  }

}