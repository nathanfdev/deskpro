import PropTypes from 'prop-types';
// @flow
import React from 'react';
import { Map, List as ImmutableList } from 'immutable';
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

const renderFeedback = (currentViewMode, toggleSelected) => {
  if (currentViewMode === constants.VIEW_MODE_CARD) {
    return <FeedbackCardsContainer toggleSelected={toggleSelected} />;
  }
  return <FeedbackTableContainer />;
};

const renderComments = (currentViewMode, toggleSelected) => {
  if (currentViewMode === constants.VIEW_MODE_CARD) {
    return <CommentCardsContainer toggleSelected={toggleSelected} />;
  }
  return <CommentTableContainer />;
};

const contentChoice = (isComments, currentViewMode, toggleSelected) => {
  if (isComments) {
    return renderComments(currentViewMode, toggleSelected);
  }
  return renderFeedback(currentViewMode, toggleSelected);
};

export const List = (props:{
  isLoaded: boolean,
  isComments: boolean,
  currentViewMode: string,
  handlePageClick: (page:number) => void,
  toggleSelected: (id:number) => void,
  selected: ImmutableList,
  currentListParams: Map,
  pagination: Map,
  fields: Map
}) => {
  const { isLoaded, isComments, currentViewMode, pagination, selected, currentListParams, fields } = props;
  const { toggleSelected, handlePageClick } = props;
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
        {selected.size === 0 ? <ControlBarContainer key="1" /> : <MassActionContainer key="2" />}
      </ListFrameMenu>
      <ListFrameContents isLoaded={isLoaded}>
        <SaveAsCsv
          currentListParams={params}
          exportedFields={exportedFields.toArray()}
          content={content}
        />
        {contentChoice(isComments, currentViewMode, toggleSelected)}
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
};

List.propTypes = {
  isLoaded:          PropTypes.bool,
  isComments:        PropTypes.bool,
  selected:          PropTypes.object.isRequired,
  currentListParams: PropTypes.object.isRequired,
  pagination:        PropTypes.object,
  toggleSelected:    PropTypes.func.isRequired,
  handlePageClick:   PropTypes.func.isRequired,
  currentViewMode:   PropTypes.string.isRequired,
  fields:            PropTypes.object.isRequired
};
