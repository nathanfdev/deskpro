import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { connect } from 'react-redux';
import { initialLoad } from '../../Actions/feedbackNavActions';
import { applyParams } from '../../Actions/FeedbackListActions';
import { Nav } from './Nav';
import {
  categoryCountersSelector, commentsToReviewCountSelector, isLoadedSelector, feedbackToReviewCountSelector,
  typeCountersSelector, statusCountersSelector, feedbackLabelsSelector
}
  from '../../Selectors/nav';

@connect(state => ({
  isLoaded:              isLoadedSelector(state),
  feedbackToReviewCount: feedbackToReviewCountSelector(state),
  commentsToReviewCount: commentsToReviewCountSelector(state),
  statuses:              statusCountersSelector(state),
  types:                 typeCountersSelector(state),
  labels:                feedbackLabelsSelector(state),
  categories:            categoryCountersSelector(state)
}))

export class NavContainer extends Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired
  };

  componentDidMount() {
    this.props.dispatch(initialLoad());
  }

  render() {
    return <Nav {...this.props} onLabelClick={applyParams} />;
  }
}
