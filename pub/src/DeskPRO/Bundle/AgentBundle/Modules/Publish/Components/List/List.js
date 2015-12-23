import React, {Component, PropTypes} from 'react';
import { ListFrameContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import { ListFrameMenu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ListFrameMenu';
import { ListFrameContents } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrameContents';
import { LoadIndicator } from 'DeskPRO/Component/LoadIndicator';
import { ControlBarContainer } from './ControlBar/ControlBarContainer';
import { MassActionContainer } from './ControlBar/MassActionContainer';
import { TableContainer } from './View/Table/TableContainer';
import { CardsContainer } from './View/Cards/CardsContainer';
import { PaginationContainer } from './PaginationContainer';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';

export class List extends Component {
  static propTypes = {
    loaded: PropTypes.bool.isRequired,
    pagination: PropTypes.object,
    selected: PropTypes.object.isRequired,
    currentViewMode: PropTypes.string.isRequired
  };

  renderElements() {
    const { currentViewMode } = this.props;

    switch (currentViewMode) {
      case constants.VIEW_MODE_CARD:
        return (
          <CardsContainer/>
        );
      case constants.VIEW_MODE_TABLE:
        return (
          <TableContainer/>
        );
      default:
        throw new Error(`Unknown "${currentViewMode}" view type`);
    }
  }

  render() {
    const { loaded, pagination, selected } = this.props;
    const checkbox = {
      count: selected.size, action: ()=> {
      }
    };

    return (
      <ListFrameContainer>
        <ListFrameMenu checkbox={checkbox}>
          {!selected.size && <ControlBarContainer key="1"/>}
          {selected.size && <MassActionContainer key="1"/>}
        </ListFrameMenu>
        <LoadIndicator loaded={loaded}
                       opacity={0}
                       width={3}>
          <ListFrameContents>
            {this.renderElements()}
            {pagination && pagination.total_pages > 1 && <PaginationContainer/>}
          </ListFrameContents>
        </LoadIndicator>
      </ListFrameContainer>
    );
  }
}
