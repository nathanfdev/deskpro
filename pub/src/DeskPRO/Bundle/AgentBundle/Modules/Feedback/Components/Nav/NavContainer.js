import React from 'react';
import { Nav } from './Nav';
import * as actions from '../../Actions/FeedbackListActions'
import $ from "jquery";

import { connect } from 'react-redux';
@connect(state =>  state.FeedbackList)

export class NavContainer extends React.Component {

  constructor(props) {
    super(props);
    const { dispatch, query, order, filters, sortOptions } = this.props;
    dispatch(actions.feedbackToValidate());
    dispatch(actions.commentsToReview());
    dispatch(actions.feedbackLabels());
    dispatch(actions.feedbackTypes());
    dispatch(actions.feedbackCustomCategories());
    dispatch(actions.feedbackNew());
    dispatch(actions.feedbackActiveStatus());
    dispatch(actions.feedbackClosedStatus());
    dispatch(actions.feedbackHiddenStatus());
    let sort = sortOptions.find((option)=>option.current === true).field;
    dispatch(actions.loadFeedbackList(query, sort, order, filters));
    dispatch(actions.getFilterValues(filters.alias));
    dispatch(actions.getDisplayFieldsFromPersonSetting());
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
