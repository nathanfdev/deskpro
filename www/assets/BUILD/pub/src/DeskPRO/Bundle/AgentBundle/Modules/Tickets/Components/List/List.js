import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { ListFrameContainer, ListFrameContents, SaveAsCsv }
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

  renderCsvImportButton() {
    const { viewMode, fieldsConfig, currentListParams } = this.props;

    return <SaveAsCsv
      currentListParams={currentListParams.toJS()}
      exportedFields={fieldsConfig.get(viewMode).toArray()}
      content="Ticket"
    />;
  }

  renderElements() {
    return this.props.viewMode === constants.VIEW_MODE_TABLE ? <ListTableViewContainer /> : <ListCardViewContainer />;
  }

  renderPagination() {
    const { pagination, handlePageClick } = this.props;

    if (pagination && pagination.get('total_pages') > 1) {
      return <PaginationBoxView
        breakLabel={<li><span className="pagination-dots">&hellip;</span></li>}
        pageNum={pagination.get('total_pages')}
        currentPage={pagination.get('current_page')}
        clickCallback={handlePageClick}
      />;
    }
  }

  render() {
    const { isLoaded, selected } = this.props;

    return (
      <ListFrameContainer>
        <ListFrameMenu>
          {selected.size ? <MassActionContainer /> : <ControlBarContainer />}
        </ListFrameMenu>
        <ListFrameContents isLoaded={isLoaded}>
          {this.renderCsvImportButton()}
          {this.renderElements()}
          {this.renderPagination()}
        </ListFrameContents>
      </ListFrameContainer>
    );
  }
}
