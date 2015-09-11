import React from 'react';
import { connect } from 'redux/react';
import { OrderBy } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';
import { toggleSort, toggleOrder } from 'DeskPRO/Bundle/AgentBundle/Modules/CRM/Actions/crmNavActions';

@connect(state => ({
  sort: state.FeedbackList.sort,
  sortName: state.FeedbackList.sortName,
  sortOptions: state.FeedbackList.sortOptions,
  order: state.FeedbackList.order,
  query: state.FeedbackList.query
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
    const {dispatch, order, query} = this.props;
    dispatch(toggleSort(query, newSort, newSortName, order));
  }

  toggleListOrder() {
    const {dispatch, query, sort, order} = this.props;
    dispatch(toggleOrder(query, sort, order));
  }

}