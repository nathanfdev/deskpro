import PropTypes from 'prop-types';
import React from 'react';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { ListFrameContainer, SaveAsCsv } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame';
import { ListFrameMenu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ListFrameMenu';
import { ListFrameContents } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ListFrameContents';
import { PaginationBoxView } from '../../../Common/Components/Pagination/PaginationBoxView';
import { ControlBarContainer } from './ControlBar/ControlBarContainer';
import { ChatsCardsContainer } from './View/List/ChatsCardsContainer';
import { ChatsTableContainer } from './View/Table/ChatsTableContainer';

export class List extends React.Component {
  static propTypes = {
    isLoaded:          PropTypes.bool.isRequired,
    pagination:        PropTypes.object,
    toggleSelected:    PropTypes.func.isRequired,
    handlePageClick:   PropTypes.func.isRequired,
    elements:          PropTypes.object.isRequired,
    currentListParams: PropTypes.object.isRequired,
    viewMode:          PropTypes.string.isRequired,
    cardFields:        PropTypes.object.isRequired,
    tableFields:       PropTypes.object.isRequired
  };

  render() {
    const { isLoaded, pagination, viewMode, cardFields, tableFields, currentListParams } = this.props;
    const { toggleSelected, handlePageClick } = this.props;
    const exportedFields = viewMode === constants.VIEW_MODE_CARD ? cardFields : tableFields;
    let params           = currentListParams.toJS();
    const { navItem } = params;
    if (navItem) {
      delete params.navItem;
      params = { ...params, ...navItem };
    }

    return (
      <ListFrameContainer>
        <ListFrameMenu>
          <ControlBarContainer />
        </ListFrameMenu>
        <ListFrameContents isLoaded={isLoaded}>
          <SaveAsCsv
            currentListParams={params}
            exportedFields={exportedFields.toArray()}
            content="UserChat"
          />
          {viewMode === constants.VIEW_MODE_CARD
            ? <ChatsCardsContainer toggleSelected={toggleSelected} fields={cardFields} />
            : <ChatsTableContainer fields={tableFields} />
          }
          {pagination && pagination.total_pages > 1 ?
            <PaginationBoxView
              breakLabel={<li><span className="pagination-dots">&hellip;</span></li>}
              pageNum={pagination.total_pages}
              currentPage={pagination.current_page}
              clickCallback={handlePageClick}
            />
            : null
          }
        </ListFrameContents>
      </ListFrameContainer>
    );
  }
}
