import React, {Component, PropTypes} from 'react';
import { ListFrameContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import { FeedbackListControlBar } from './ControlBar/FeedbackListControlBar';
import { FeedbackCardsContainer } from './View/List/FeedbackCardsContainer';
import { FeedbackCommentList } from './View/List/FeedbackCommentList';
import { FeedbackTableContainer } from './View/Table/FeedbackTableContainer';
import { FeedbackCommentTableContainer } from './View/Table/FeedbackCommentTableContainer';
import ListFrameContents from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrameContents';

import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';

export class List extends Component {

  static propTypes = {
    feedback: PropTypes.array.isRequired,
    people: PropTypes.array.isRequired,
    emails: PropTypes.array.isRequired,
    selected: PropTypes.array.isRequired,
    feedbackFromStore: PropTypes.array.isRequired,
    feedbackStatuses: PropTypes.object.isRequired,
    dispatch: PropTypes.func.isRequired,
    toggleSelected: PropTypes.func.isRequired,
    comments: PropTypes.array.isRequired,
    massAction: PropTypes.bool.isRequired,
    currentViewMode: PropTypes.string.isRequired,
    isComments: PropTypes.bool
  };

  contentChoice() {
    if (this.props.isComments) {
      return this.renderComments();
    }
    return this.renderFeedback();
  }

  renderFeedback() {
    const { currentViewMode, toggleSelected } = this.props;

    if (currentViewMode === constants.VIEW_MODE_CARD) {
      return (
        <FeedbackCardsContainer
          toggleSelected={toggleSelected}
          />
      );
    }
    return (
      <FeedbackTableContainer/>
    );
  }

  renderComments() {
    const {dispatch, feedbackFromStore, currentViewMode, comments, selected, toggleSelected, massAction, people, emails, feedbackStatuses} = this.props;
    if (currentViewMode === constants.VIEW_MODE_CARD) {
      return (
        <FeedbackCommentList
          dispatch={dispatch}
          comments={comments}
          selected={selected}
          toggleSelected={toggleSelected}
          people={people}
          emails={emails}
          massAction={massAction}
          feedback={feedbackFromStore}
          />
      );
    }
    return (
      <FeedbackCommentTableContainer
        elements={comments}
        feedbackStatuses={feedbackStatuses}
        />
    );
  }

  render() {
    const {dispatch, feedback} = this.props;
    return (
      <ListFrameContainer>
        <FeedbackListControlBar
          count={feedback ? feedback.size : 0}
          dispatch={dispatch}
          />
        <ListFrameContents>
          {this.contentChoice()}
        </ListFrameContents>
      </ListFrameContainer>
    );
  }

}
