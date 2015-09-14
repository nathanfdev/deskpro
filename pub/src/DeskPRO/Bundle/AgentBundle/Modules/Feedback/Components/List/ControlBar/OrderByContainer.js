import React from 'react';
import { connect } from 'react-redux';
import { OrderBy } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';
import { toggleSort, toggleOrder } from 'DeskPRO/Bundle/AgentBundle/Modules/Feedback/Actions/FeedbackListActions';

@connect(state => ({
  sort:        state.FeedbackList.sort,
  sortName:    state.FeedbackList.sortName,
  sortOptions: state.FeedbackList.sortOptions,
  order:       state.FeedbackList.order,
  filters:     state.FeedbackList.filters,
  query:       state.FeedbackList.query
}))
export class OrderByContainer extends React.Component {

  render() {
    const { sort, sortName, sortOptions, order } = this.props;

    return (
      <OrderBy
        sort={sort}
        sortName={sortName}
        sortOptions={sortOptions}
        order={order}
        toggleSort={this.toggleListSort.bind(this)}
        toggleOrder={this.toggleListOrder.bind(this)}
        />
    );
  }


  /** Change sort option (Order By ...)*/
  toggleListSort(newSort, newSortName) {
    const {dispatch, order, query, filters} = this.props;
    dispatch(toggleSort(query, newSort, newSortName, order, filters));
  }

  toggleListOrder() {
    const {dispatch, query, sort, order, filters} = this.props;
    dispatch(toggleOrder(query, sort, order, filters));
  }

}