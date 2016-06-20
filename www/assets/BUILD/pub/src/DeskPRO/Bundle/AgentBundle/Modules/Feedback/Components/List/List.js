import React, { Component, PropTypes } from 'react';
import {
  ListFrameContainer, ListFrameContents, SaveAsCsv
} from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame';
import { ListFrameMenu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ListFrameMenu';
import { PaginationBoxView } from '../../../Common/Components/Pagination/PaginationBoxView';
import { ControlBarContainer } from './ControlBar/ControlBarContainer';
import { MassActionContainer } from './ControlBar/MassActionContainer';
import { FeedbackCardsContainer } from './View/Card/FeedbackCardsContainer';
import { CommentCardsContainer } from './View/Card/CommentCardsContainer';
import { FeedbackTableContainer } from './View/Table/FeedbackTableContainer';
import { CommentTableContainer } from './View/Table/CommentTableContainer';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';

export class List extends Component {
  static propTypes = {
    isLoaded:          PropTypes.bool,
    isComments:        PropTypes.bool,
    selected:          PropTypes.object.isRequired,
    currentListParams: PropTypes.object.isRequired,
    pagination:        PropTypes.object,
    toggleSelected:    PropTypes.func.isRequired,
    handlePageClick:   PropTypes.func.isRequired,
    currentViewMode:   PropTypes.string.isRequired,
    fields:            PropTypes.object.isRequired,
    elements:          PropTypes.object.isRequired
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
    const { currentViewMode, toggleSelected } = this.props;
    if (currentViewMode === constants.VIEW_MODE_CARD) {
      return <CommentCardsContainer toggleSelected={toggleSelected} />;
    }
    return <CommentTableContainer />;
  }

  render() {
    const { isLoaded, pagination, selected, handlePageClick, isComments } = this.props;
    const { currentListParams, currentViewMode, fields } = this.props;
    const exportedFields = isComments
      ? fields.get('comments').get(currentViewMode)
      : fields.get('feedback').get(currentViewMode);

    const content        = isComments ? 'FeedbackComment' : 'Feedback';
    let params           = currentListParams.toJS();
    const { navItem } = params;
    if (navItem) {
      delete params.navItem;
      params = { ...params, ...navItem };
    }

    return (
      <ListFrameContainer>
        <ListFrameMenu>
          {!selected.size && <ControlBarContainer key="1" />}
          {selected.size && <MassActionContainer key="2" />}
        </ListFrameMenu>
        <ListFrameContents isLoaded={isLoaded}>
          <SaveAsCsv
            currentListParams={params}
            exportedFields={exportedFields.toArray()}
            content={content}
          />
          {this.contentChoice()}
          {pagination && pagination.get('total_pages') > 1
          && <PaginationBoxView
            breakLabel={<li><span className="pagination-dots">&hellip;</span></li>}
            pageNum={pagination.get('total_pages')}
            currentPage={pagination.get('current_page')}
            clickCallback={handlePageClick}
          />
          }
        </ListFrameContents>
      </ListFrameContainer>
    );
  }

}
