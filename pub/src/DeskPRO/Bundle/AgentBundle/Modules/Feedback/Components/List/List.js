import React, {Component, PropTypes} from 'react';
import { ListFrameContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import { ControlBarContainer } from './ControlBar/ControlBarContainer';
import { FeedbackCardsContainer } from './View/List/FeedbackCardsContainer';
import { FeedbackCommentList } from './View/List/FeedbackCommentList';
import { FeedbackTableContainer } from './View/Table/FeedbackTableContainer';
import { FeedbackCommentTableContainer } from './View/Table/FeedbackCommentTableContainer';
import ListFrameContents from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrameContents';

import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';

export class List extends Component {

  static propTypes = {
    elements: PropTypes.object.isRequired,
    isComments: PropTypes.bool,
    people: PropTypes.object.isRequired,
    emails: PropTypes.object.isRequired,
    selected: PropTypes.object.isRequired,
    feedbackFromStore: PropTypes.object.isRequired,
    feedbackStatuses: PropTypes.object.isRequired,
    dispatch: PropTypes.func.isRequired,
    toggleSelected: PropTypes.func.isRequired,
    comments: PropTypes.object.isRequired,
    massAction: PropTypes.bool.isRequired,
    currentViewMode: PropTypes.string.isRequired
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
    const {dispatch, elements, feedbackFromStore, currentViewMode, selected, toggleSelected, massAction, people, emails, feedbackStatuses} = this.props;
    if (currentViewMode === constants.VIEW_MODE_CARD) {
      return (
        <FeedbackCommentList
          dispatch={dispatch}
          comments={elements}
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
        elements={elements}
        feedbackStatuses={feedbackStatuses}
        />
    );
  }

  render() {
    return (
      <ListFrameContainer>
        <ControlBarContainer />
        <ListFrameContents>
          {this.contentChoice()}
        </ListFrameContents>
      </ListFrameContainer>
    );
  }

}
