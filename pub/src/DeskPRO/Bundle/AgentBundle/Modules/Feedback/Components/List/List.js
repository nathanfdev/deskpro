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
    currentViewMode: PropTypes.object.isRequired,
  };


  render() {
    return (
      <ListFrame>
        <FeedbackListControlBar />
        {this.contentChoice()}
      </ListFrame>
    );
  }

  contentChoice() {
    const {currentContent} = this.props;
    if (currentContent === 'comments') {
      return this.renderComments();
    } else {
      return this.renderFeedback();
    }
  }

  renderFeedback() {
    const {currentViewMode, feedback, people, feedbackTypes} = this.props;
    var viewMode = currentViewMode.field;
    if (viewMode === constants.VIEW_MODE_LIST) {
      return (
        <FeedbackList elements={feedback} people={people} feedbackTypes={feedbackTypes}/>
      );
    }
    else {
      return (
        <FeedbackTable elements={feedback} people={people}/>
      );
    }
  }

  renderComments() {
    const {currentViewMode, comments} = this.props;
    var viewMode = currentViewMode.field;
    if (viewMode === constants.VIEW_MODE_LIST) {
      return (
        <FeedbackCommentList elements={comments}/>
      );
    }
    else {
      return (
        <FeedbackCommentTable elements={comments}/>
      );
    }
  }
}
