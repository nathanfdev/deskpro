import React, {Component, PropTypes} from 'react';
import { ListFrameContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import { ListFrameMenu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ListFrameMenu';
import { ListFrameContents } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrameContents';
import { ControlBarContainer } from './ControlBar/ControlBarContainer';
import { MassActionContainer } from './ControlBar/MassActionContainer';
import { TableContainer } from './View/Table/TableContainer';
import { CardsContainer } from './View/Cards/CardsContainer';
import { PaginationContainer } from './PaginationContainer';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';

export class List extends Component {
  static propTypes = {
    isLoaded: PropTypes.bool.isRequired,
    pagination: PropTypes.object,
    selected: PropTypes.object.isRequired,
    currentViewMode: PropTypes.string.isRequired
  };

  render() {
    const { isLoaded, pagination, selected, currentViewMode } = this.props;
    const checkbox = {
      count: selected.size, action: ()=> {
      }
    };

    return (
      <ListFrameContainer>
        <ListFrameMenu checkbox={checkbox}>
          {!selected.size && <ControlBarContainer key="1"/>}
          {selected.size && <MassActionContainer key="2"/>}
        </ListFrameMenu>
        <ListFrameContents isLoaded={isLoaded}>
          {currentViewMode == constants.VIEW_MODE_TABLE ? <TableContainer /> : <CardsContainer />}
          {pagination && pagination.total_pages > 1 && <PaginationContainer/>}
        </ListFrameContents>
      </ListFrameContainer>
    );
  }
}
