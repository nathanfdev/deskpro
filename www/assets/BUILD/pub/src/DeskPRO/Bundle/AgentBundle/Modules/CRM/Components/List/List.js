import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { ListFrameContainer, SaveAsCsv } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame';
import { ListFrameMenu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ListFrameMenu';
import { ListFrameContents } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ListFrameContents';
import { PaginationBoxView } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Pagination/PaginationBoxView';
import { CrmCardContainer } from './View/Card/CrmCardContainer';
import { CrmTableContainer } from './View/Table/CrmTableContainer';
import { ControlBarContainer } from './ControlBar/ControlBarContainer';
import { MassActionContainer } from './ControlBar/MassActionContainer';

export class List extends Component {
  static propTypes = {
    selected:          PropTypes.object.isRequired,
    currentListParams: PropTypes.object.isRequired,
    currentViewMode:   PropTypes.string.isRequired,
    content:           PropTypes.string.isRequired,
    handlePageClick:   PropTypes.func.isRequired,
    isLoaded:          PropTypes.bool,
    pagination:        PropTypes.object,
    peopleFields:      PropTypes.object.isRequired,
    orgFields:         PropTypes.object.isRequired
  };

  render() {
    const {
            currentListParams, currentViewMode, selected, isLoaded, pagination, content, handlePageClick,
            peopleFields, orgFields
          } = this.props;

    const exportedFields = content === 'people' ? peopleFields.get(currentViewMode) : orgFields.get(currentViewMode);
    const repositoryName = content === 'people' ? 'Person' : 'Organization';
    let params = currentListParams.toJS();
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
            content={repositoryName}
          />
          {currentViewMode === constants.VIEW_MODE_CARD ?
            <CrmCardContainer
              content={content}
              peopleFields={peopleFields.get(constants.VIEW_MODE_CARD)}
              orgFields={orgFields.get(constants.VIEW_MODE_CARD)}
            />
            :
            <CrmTableContainer
              content={content}
              peopleFields={peopleFields.get(constants.VIEW_MODE_TABLE)}
              orgFields={orgFields.get(constants.VIEW_MODE_TABLE)}
            />
          }
          {pagination && pagination.get('total_pages') > 1
          && <PaginationBoxView
            breakLabel={<li><span className="pagination-dots">&hellip;</span></li>}
            pageNum={pagination.get('total_pages')}
            currentPage={pagination.get('current_page')}
            clickCallback={handlePageClick}
          />}
        </ListFrameContents>
      </ListFrameContainer>
    );
  }

}
