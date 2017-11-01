import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { ListFrameContainer, SaveAsCsv } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame';
import { ListFrameMenu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ListFrameMenu';
import { ListFrameContents } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ListFrameContents';
import { ControlBarContainer } from './ControlBar/ControlBarContainer';
import { MassActionContainer } from './ControlBar/MassActionContainer';
import { TableContainer } from './View/Table/TableContainer';
import { CardsContainer } from './View/Cards/CardsContainer';
import { PaginationContainer } from './PaginationContainer';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';

export class List extends Component {
  static propTypes = {
    isLoaded:          PropTypes.bool.isRequired,
    pagination:        PropTypes.object,
    selected:          PropTypes.object.isRequired,
    fields:            PropTypes.object.isRequired,
    currentListParams: PropTypes.object.isRequired,
    currentViewMode:   PropTypes.string.isRequired,
    content:           PropTypes.string.isRequired
  };

  render() {
    const { isLoaded, pagination, selected, fields, content, currentViewMode, currentListParams } = this.props;
    const exportedFields = fields.get(content).get(currentViewMode);
    let params = currentListParams.toJS();
    const { navItem } = params;
    if (navItem) {
      delete params.navItem;
      params = { ...params, ...navItem };
    }

    const checkbox       = {
      count:  selected.size,
      action: () => {
      }
    };

    return (
      <ListFrameContainer>
        <ListFrameMenu checkbox={checkbox}>
          {!selected.size && <ControlBarContainer key="1" />}
          {selected.size && <MassActionContainer key="2" />}
        </ListFrameMenu>
        <ListFrameContents isLoaded={isLoaded}>
          <SaveAsCsv
            currentListParams={params}
            exportedFields={exportedFields.toArray()}
            content="Content"
          />
          {currentViewMode === constants.VIEW_MODE_TABLE ? <TableContainer /> : <CardsContainer />}
          {pagination && pagination.total_pages > 1 && <PaginationContainer />}
        </ListFrameContents>
      </ListFrameContainer>
    );
  }
}
