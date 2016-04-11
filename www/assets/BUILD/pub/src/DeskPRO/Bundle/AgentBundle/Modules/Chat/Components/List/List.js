import React, { PropTypes } from 'react';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { ListFrameContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame';
import { ListFrameMenu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ListFrameMenu';
import { ListFrameContents } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ListFrameContents';
import { PaginationBoxView } from '../../../Common/Components/Pagination/PaginationBoxView';
import { ControlBarContainer } from './ControlBar/ControlBarContainer';
import { ChatsCardsContainer } from './View/List/ChatsCardsContainer';
import { ChatsTableContainer } from './View/Table/ChatsTableContainer';

export class List extends React.Component {
  static propTypes = {
    isLoaded:        PropTypes.bool.isRequired,
    pagination:      PropTypes.object,
    toggleSelected:  PropTypes.func.isRequired,
    handlePageClick: PropTypes.func.isRequired,
    elements:        PropTypes.array.isRequired,
    viewMode:        PropTypes.string.isRequired
  };

  render() {
    const { isLoaded, pagination, viewMode, toggleSelected, handlePageClick } = this.props;

    return (
      <ListFrameContainer>
        <ListFrameMenu>
          <ControlBarContainer />
        </ListFrameMenu>
        <ListFrameContents isLoaded={isLoaded}>
          {viewMode === constants.VIEW_MODE_CARD ? <ChatsCardsContainer toggleSelected={toggleSelected} /> :
            <ChatsTableContainer />}
          {pagination && pagination.total_pages > 1 &&
          <PaginationBoxView breakLabel={<li><span className="pagination-dots">&hellip;</span></li>}
                             pageNum={pagination.total_pages}
                             currentPage={pagination.current_page}
                             clickCallback={handlePageClick} />}
        </ListFrameContents>
      </ListFrameContainer>
    );
  }
}
