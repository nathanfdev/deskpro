import React, {Component, PropTypes} from 'react';
import { ListFrame }  from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';
import { FeedbackListControlBar } from './ControlBar/FeedbackListControlBar';
import { FeedbackList } from './View/List/FeedbackList';
import { FeedbackCommentList } from './View/List/FeedbackCommentList';
import { FeedbackTableContainer } from './View/Table/FeedbackTableContainer';
import { FeedbackCommentTableContainer } from './View/Table/FeedbackCommentTableContainer';
import ListFrameContents from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrameContents';

import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';

export class List extends Component {

  static propTypes = {
    feedback: PropTypes.array.isRequired,
    people: PropTypes.array.isRequired,
    selected: PropTypes.array.isRequired,
    feedbackFromStore: PropTypes.array.isRequired,
    toggleSelected: PropTypes.func.isRequired,
    comments: PropTypes.array.isRequired,
    massAction: PropTypes.bool.isRequired,
    currentViewMode: PropTypes.object.isRequired,
    currentGroup: PropTypes.object.isRequired
  };

  contentChoice() {
    const {currentGroup} = this.props;
    if (currentGroup.name === 'feedback_comments') {
      return this.renderComments();
    }
    return this.renderFeedback();
  }

  renderFeedback() {
    const {
            currentViewMode, feedback, selected, toggleSelected, people, feedbackTypes, massAction, feedbackLabels,
            feedbackComments, feedbackStatuses } = this.props;

    var viewMode = currentViewMode.field;
    if (viewMode === constants.VIEW_MODE_LIST) {
      return (
        <FeedbackList
          elements={feedback}
          selected={selected}
          toggleSelected={toggleSelected}
          people={people}
          feedbackLabels={feedbackLabels}
          feedbackTypes={feedbackTypes}
          feedbackComments={feedbackComments}
          feedbackStatuses={feedbackStatuses}
          massAction={massAction}
          />
      );
    }
    return (
      <FeedbackTableContainer
        elements={feedback}
        selected={selected}
        toggleSelected={toggleSelected}
        people={people}
        />
    );
  }

  renderComments() {
    const {feedbackFromStore, currentViewMode, comments, selected, toggleSelected, massAction, people} = this.props;
    var viewMode = currentViewMode.field;
    if (viewMode === constants.VIEW_MODE_LIST) {
      return (
        <FeedbackCommentList
          comments={comments}
          selected={selected}
          toggleSelected={toggleSelected}
          people={people}
          massAction={massAction}
          feedback={feedbackFromStore}
          />
      );
    }
    return (
      <FeedbackCommentTableContainer elements={comments}/>
    );
  }

  render() {
    return (
      <ListFrame>
        <FeedbackListControlBar count={this.props.feedback ? this.props.feedback.size : 0}/>
        <ListFrameContents>
          {this.contentChoice()}
        </ListFrameContents>
      </ListFrame>
    );
  }

}
