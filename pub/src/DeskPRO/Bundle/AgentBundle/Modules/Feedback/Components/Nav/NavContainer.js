import React from 'react';
import { connect } from 'react-redux';
import * as actions from '../../Actions/FeedbackListActions'
import { Nav } from './Nav';
import $ from "jquery";
import { sortingDataSelector } from '../../Selectors/list';

@connect(state => {
  return ({
    query: state.Feedback.nav.get('query'),
    order: state.Feedback.nav.get('order'),
    filters: state.Feedback.nav.get('filters'),
    toValidateCount: state.Feedback.nav.get('toValidateCount'),
    statuses: state.Feedback.nav.get('statuses').toJS(),
    types: state.Feedback.nav.get('types'),
    labels: state.Feedback.nav.get('labels'),
    customCategories: state.Feedback.nav.get('customCategories'),
    currentSortMode: sortingDataSelector(state)
  });
})

export class NavContainer extends React.Component {

  constructor(props) {
    super(props);
    const { dispatch, query, order, filters, currentSortMode } = this.props;
    dispatch(actions.feedbackToValidate());
    dispatch(actions.commentsToReview());
    dispatch(actions.feedbackLabels());
    dispatch(actions.feedbackTypes());
    dispatch(actions.feedbackCustomCategories());
    dispatch(actions.feedbackNew());
    dispatch(actions.feedbackActiveStatus());
    dispatch(actions.feedbackClosedStatus());
    dispatch(actions.feedbackHiddenStatus());
    dispatch(actions.getFilterValues(filters.alias));
    dispatch(actions.getDisplayFieldsFromPersonSetting());
    //dispatch(actions.loadFeedbackList(query, currentSortMode.field, order, filters));
  }

  render() {
    return (
      <Nav
        toValidateCount={this.props.toValidateCount}
        groupChoice={this.groupChoice.bind(this)}
        dispatch={this.props.dispatch}
        statuses={this.props.statuses}
        labels={this.props.labels}
        types={this.props.types}
        customCategories={this.props.customCategories}
        />
    );
  }


  groupChoice(params, event) {
    event.preventDefault();
    event.stopPropagation();
    $('.sidebar-list a.item, .sidebar-list a.item-label').removeClass('active');
    $(event.target).closest('a').addClass('active');
    const {dispatch, sortOptions, order, filters } = this.props;
    let sort = sortOptions.find((option)=>option.current === true).field;
    dispatch(actions.changeQueryState(params, sort, order, filters));
  }
}
