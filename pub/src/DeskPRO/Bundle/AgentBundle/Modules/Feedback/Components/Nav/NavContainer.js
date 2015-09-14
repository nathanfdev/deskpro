import React from 'react';
import { Nav } from './Nav';
import * as actions from '../../Actions/FeedbackListActions'
import $ from "jquery";

import { connect } from 'redux/react';
@connect(state =>  state.FeedbackList)

export class NavContainer extends React.Component {

  constructor(props) {
    super(props);
    const { dispatch, query, sort, order, filters } = this.props;
    dispatch(actions.feedbackToValidate());
    dispatch(actions.commentsToReview());
    dispatch(actions.feedbackLabels());
    dispatch(actions.feedbackTypes());
    dispatch(actions.feedbackCustomCategories());
    dispatch(actions.feedbackNew());
    dispatch(actions.feedbackActiveStatus());
    dispatch(actions.feedbackClosedStatus());
    dispatch(actions.feedbackHiddenStatus());
    dispatch(actions.loadFeedbackList(query, sort, order, filters));
    dispatch(actions.getFilterValues(filters.alias));
  }

  render() {

    return (
      <Nav
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
    const {dispatch, sort, order, filters } = this.props;
    dispatch(actions.changeQueryState(params, sort, order, filters));
  }
}
