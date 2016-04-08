import React, { Component, PropTypes } from 'react';
import { ListFrameContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame';
import { ListFrameMenu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ListFrameMenu';
import { ListFrameContents } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ListFrameContents';
import { ControlBarContainer } from './ControlBar/ControlBarContainer';
import { MassActionContainer } from './ControlBar/MassActionContainer';
import { FeedbackCardsContainer } from './View/Card/FeedbackCardsContainer';
import { CommentCardsContainer } from './View/Card/CommentCardsContainer';
import { FeedbackTableContainer } from './View/Table/FeedbackTableContainer';
import { CommentTableContainer } from './View/Table/CommentTableContainer';
import { PaginationContainer } from './PaginationContainer';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';

export class List extends Component {
  static propTypes = {
    isLoaded:        PropTypes.bool,
    isComments:      PropTypes.bool,
    selected:        PropTypes.object.isRequired,
    pagination:      PropTypes.object,
    toggleSelected:  PropTypes.func.isRequired,
    currentViewMode: PropTypes.string.isRequired
  };

  constructor(props) {
    super(props);

    this.state = {
      data:   [],
      offset: 0
    };
  }

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
        <FeedbackCardsContainer toggleSelected={toggleSelected} />
      );
    }
    return (
      <FeedbackTableContainer />
    );
  }

  renderComments() {
    const { currentViewMode, selected, toggleSelected } = this.props;
    if (currentViewMode === constants.VIEW_MODE_CARD) {
      return (
        <CommentCardsContainer selected={selected}
                               toggleSelected={toggleSelected} />
      );
    }
    return (
      <CommentTableContainer />
    );
  }

  render() {
    const { isLoaded, pagination, selected } = this.props;

    return (
      <ListFrameContainer>
        <ListFrameMenu>
          {!selected.size && <ControlBarContainer key="1" />}
          {selected.size && <MassActionContainer key="2" />}
        </ListFrameMenu>
        <ListFrameContents isLoaded={isLoaded}>
          {this.contentChoice()}
          {pagination && pagination.get('total_pages') > 1 && <PaginationContainer />}
        </ListFrameContents>
      </ListFrameContainer>
    );
  }

}
