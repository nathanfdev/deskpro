import React, {Component, PropTypes} from 'react';
import { ListFrameContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import { ListFrameMenu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ListFrameMenu';
import { ControlBarContainer } from './ControlBar/ControlBarContainer';
import { MassActionContainer } from './ControlBar/MassActionContainer';
import { FeedbackCardsContainer } from './View/List/FeedbackCardsContainer';
import { FeedbackCommentsCardsContainer } from './View/List/FeedbackCommentsCardsContainer';
import { FeedbackTableContainer } from './View/Table/FeedbackTableContainer';
import { FeedbackCommentTableContainer } from './View/Table/FeedbackCommentTableContainer';
import { ListFrameContents } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrameContents';
import { LoadIndicator } from 'DeskPRO/Component/LoadIndicator';
import { PaginationContainer } from './PaginationContainer';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';

export class List extends Component {

  static propTypes = {
    loaded: PropTypes.bool.isRequired,
    currentApp: PropTypes.string.isRequired,
    isComments: PropTypes.bool,
    selected: PropTypes.object.isRequired,
    pagination: PropTypes.object,
    toggleSelected: PropTypes.func.isRequired,
    currentViewMode: PropTypes.string.isRequired
  };

  constructor(props) {
    super(props);

    this.state = {
      data: [],
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
        <FeedbackCardsContainer toggleSelected={toggleSelected}/>
      );
    }
    return (
      <FeedbackTableContainer/>
    );
  }

  renderComments() {
    const { currentViewMode, selected, toggleSelected } = this.props;
    if (currentViewMode === constants.VIEW_MODE_CARD) {
      return (
        <FeedbackCommentsCardsContainer selected={selected}
                                        toggleSelected={toggleSelected}/>
      );
    }
    return (
      <FeedbackCommentTableContainer/>
    );
  }

  render() {
    const { loaded, pagination, selected } = this.props;

    return (
      <ListFrameContainer>
        <ListFrameMenu>
          {!selected.size && <ControlBarContainer key="1"/>}
          {selected.size && <MassActionContainer key="2"/>}
        </ListFrameMenu>
        <LoadIndicator loaded={loaded}
                       opacity={0}
                       width={3}>
          <ListFrameContents>
            {this.contentChoice()}
            {pagination && pagination.total_pages > 1 && <PaginationContainer/>}
          </ListFrameContents>
        </LoadIndicator>
      </ListFrameContainer>
    );
  }

}
