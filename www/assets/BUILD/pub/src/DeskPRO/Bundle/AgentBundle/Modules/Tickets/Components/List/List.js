import React, { Component, PropTypes } from 'react';
import { ListFrameContainer, ListFrameContents } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame';
import { ControlBarContainer } from './ControlBarContainer';
import { MassActionContainer } from './MassActionContainer';
import { ListTableViewContainer } from './View/Table/ListTableViewContainer';
import { ListCardViewContainer } from './View/Card/ListCardViewContainer';
import { ListFrameMenu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ListFrameMenu';
import { PaginationContainer } from './PaginationContainer';

export class List extends Component {
  static propTypes = {
    viewMode: PropTypes.string.isRequired,
    selected: PropTypes.object.isRequired,
    isLoaded: PropTypes.bool.isRequired
  };

  render() {
    const { isLoaded, selected, viewMode } = this.props;

    return (
      <ListFrameContainer>
        <ListFrameMenu>
          {!selected.size && <ControlBarContainer key="1"/>}
          {selected.size && <MassActionContainer key="2"/>}
        </ListFrameMenu>
        <ListFrameContents isLoaded={isLoaded}>
          {viewMode === 'table' ? <ListTableViewContainer /> : <ListCardViewContainer />}
          <PaginationContainer />
        </ListFrameContents>
      </ListFrameContainer>
    );
  }
}
