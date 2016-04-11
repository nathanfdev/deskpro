import React, { Component, PropTypes } from 'react';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { ListFrameContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame';
import { ListFrameMenu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ListFrameMenu';
import { ListFrameContents } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ListFrameContents';
import { PaginationBoxView } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Pagination/PaginationBoxView';
import { CrmCardContainer } from './View/Card/CrmCardContainer';
import { CrmTableContainer } from './View/Table/CrmTableContainer';
import { ControlBarContainer } from './ControlBar/ControlBarContainer';
import { MassActionContainer } from './ControlBar/MassActionContainer';

export class List extends Component {
  static propTypes = {
    selected:        PropTypes.object.isRequired,
    isLoaded:        PropTypes.bool.isRequired,
    pagination:      PropTypes.object,
    currentViewMode: PropTypes.string.isRequired,
    handlePageClick: PropTypes.func.isRequired,
    content:         PropTypes.string.isRequired
  };

  render() {
    const { currentViewMode, selected, isLoaded, pagination, content, handlePageClick } = this.props;

    return (
      <ListFrameContainer>
        <ListFrameMenu>
          {!selected.size && <ControlBarContainer key="1" />}
          {selected.size && <MassActionContainer key="2" />}
        </ListFrameMenu>
        <ListFrameContents isLoaded={isLoaded}>
          {currentViewMode === constants.VIEW_MODE_CARD ?
            <CrmCardContainer content={content} /> :
            <CrmTableContainer content={content} />}
          {pagination && pagination.get('total_pages') > 1 &&
          <PaginationBoxView breakLabel={<li><span className="pagination-dots">&hellip;</span></li>}
                             pageNum={pagination.get('total_pages')}
                             currentPage={pagination.get('current_page')}
                             clickCallback={handlePageClick} />}
        </ListFrameContents>
      </ListFrameContainer>
    );
  }

}
