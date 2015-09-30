import React, {Component, PropTypes} from 'react';
import { ListFrame }  from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';
import { FeedbackListControlBar } from './ControlBar/FeedbackListControlBar';
import { FeedbackList } from './View/List/FeedbackList';
import { FeedbackTable } from './View/Table/FeedbackTable';

import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants'

export class List extends Component {

  static propTypes = {
    feedback: PropTypes.array.isRequired,
    comments: PropTypes.array.isRequired,
    currentContent: PropTypes.string.isRequired,
    currentViewMode: PropTypes.object.isRequired
  };

  contentChoice() {
    const {currentContent} = this.props;
    if (currentContent === 'comments') {
      return this.renderComments();
    }
    return this.renderFeedback();
  }

  renderFeedback() {
    const {currentViewMode, feedback, people, feedbackTypes, massAction, feedbackLabels, feedbackComments, feedbackStatuses} = this.props;
    var viewMode = currentViewMode.field;
    if (viewMode === constants.VIEW_MODE_LIST) {
      return (
        <FeedbackList elements={feedback} people={people} feedbackLabels={feedbackLabels} feedbackTypes={feedbackTypes} feedbackComments={feedbackComments} feedbackStatuses={feedbackStatuses} massAction={massAction}/>
      );
    }
    return (
      <FeedbackTable elements={feedback} people={people}/>
    );
  }

  renderComments() {
    const {currentViewMode, comments} = this.props;
    var viewMode = currentViewMode.field;
    if (viewMode === constants.VIEW_MODE_LIST) {
      return (
        <FeedbackCommentList elements={comments}/>
      );
    }
    return (
      <FeedbackCommentTable elements={comments}/>
    );
  }

  render() {
    return (
      <ListFrame>
        <FeedbackListControlBar count={this.props.feedback.length}/>
        {this.contentChoice()}
      </ListFrame>
    );
  }

}
