import React, { Component, PropTypes } from 'react';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import {
  ListFrameContainer, ListFrameContents, SaveAsCsv
}
  from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame';
import { PaginationBoxView } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Pagination/PaginationBoxView';
import { ControlBarContainer } from './ControlBarContainer';
import { MassActionContainer } from './MassActionContainer';
import { ListTableViewContainer } from './View/Table/ListTableViewContainer';
import { ListCardViewContainer } from './View/Card/ListCardViewContainer';
import { ListFrameMenu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ListFrameMenu';

export class List extends Component {
  static propTypes = {
    viewMode:          PropTypes.string.isRequired,
    selected:          PropTypes.object.isRequired,
    fieldsConfig:      PropTypes.object.isRequired,
    currentListParams: PropTypes.object.isRequired,
    pagination:        PropTypes.object,
    handlePageClick:   PropTypes.func.isRequired,
    isLoaded:          PropTypes.bool.isRequired
  };

  render() {
    const { isLoaded, selected, pagination, viewMode, fieldsConfig, currentListParams, handlePageClick } = this.props;

    return (
      <ListFrameContainer>
        <ListFrameMenu>
          {!selected.size && <ControlBarContainer key="1" />}
          {selected.size && <MassActionContainer key="2" />}
        </ListFrameMenu>
        <ListFrameContents isLoaded={isLoaded}>
          <SaveAsCsv
            currentListParams={currentListParams}
            exportedFields={fieldsConfig.get(viewMode).toArray()}
            content="Ticket"
          />
          {viewMode === constants.VIEW_MODE_TABLE ? <ListTableViewContainer /> : <ListCardViewContainer />}
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
