import React, {Component, PropTypes} from 'react';
import { ListFrameContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import { ControlBarContainer } from './ControlBar/ControlBarContainer';
import { FeedbackCardsContainer } from './View/List/FeedbackCardsContainer';
import { FeedbackCommentsCardsContainer } from './View/List/FeedbackCommentsCardsContainer';
import { FeedbackTableContainer } from './View/Table/FeedbackTableContainer';
import { FeedbackCommentTableContainer } from './View/Table/FeedbackCommentTableContainer';
import ListFrameContents from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrameContents';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import Loader from 'react-loader';
import { PaginationBoxView } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Pagination/PaginationBoxView';

export class List extends Component {

  static propTypes = {
    loaded: PropTypes.bool.isRequired,
    isComments: PropTypes.bool,
    selected: PropTypes.object.isRequired,
    pagination: PropTypes.object.isRequired,
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
    const { currentViewMode, selected, toggleSelected } = this.props;
    if (currentViewMode === constants.VIEW_MODE_CARD) {
      return (
        <FeedbackCommentsCardsContainer
          selected={selected}
          toggleSelected={toggleSelected}
          />
      );
    }
    return (
      <FeedbackCommentTableContainer/>
    );
  }

  renderPagination() {
    const { pagination } = this.props;
    if (pagination && pagination.total_pages > 1) {
      return (
        <PaginationBoxView breakLabel={<li className="break"><a href="">...</a></li>}
                           pageNum={pagination.total_pages}
                           currentPage={pagination.current_page}
                           marginPagesDisplayed="2"
                           pageRangeDisplayed="5"
                           clickCallback={this.handlePageClick}
                           containerClassName="pages-list"
                           subContainerClassName="pages-list"
                           activeClassName="current-page"/>
      );
    }
  }

  render() {
    const { loaded } = this.props;

    return (
      <ListFrameContainer>
        <ControlBarContainer />
        <Loader loaded={loaded}
                color="green"
                opacity={0}
                width={3}>
          <ListFrameContents>
            {this.contentChoice()}
            {this.renderPagination()}
          </ListFrameContents>
        </Loader>
      </ListFrameContainer>
    );
  }

}
