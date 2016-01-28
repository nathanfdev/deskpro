import React, { PropTypes } from 'react';
import { constants } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { ListFrameContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import { ListFrameMenu } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/ListFrameMenu';
import { ListFrameContents } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrameContents';
import { ControlBarContainer } from './ControlBar/ControlBarContainer';
import { ChatsCardsContainer } from './View/List/ChatsCardsContainer';
import { ChatsTableContainer } from './View/Table/ChatsTableContainer';
import { PaginationContainer } from './PaginationContainer';

export class List extends React.Component {
  static propTypes = {
    isLoaded: PropTypes.bool.isRequired,
    pagination: PropTypes.object,
    toggleSelected: PropTypes.func.isRequired,
    elements: PropTypes.array.isRequired,
    viewMode: PropTypes.string.isRequired
  };

  render() {
    const { isLoaded, pagination, viewMode, toggleSelected } = this.props;

    return (
      <ListFrameContainer>
        <ListFrameMenu>
          <ControlBarContainer />
        </ListFrameMenu>
          <ListFrameContents isLoaded={isLoaded}>
            {viewMode === constants.VIEW_MODE_CARD ? <ChatsCardsContainer toggleSelected={toggleSelected}/> :
              <ChatsTableContainer/>}
            {pagination && pagination.total_pages > 1 && <PaginationContainer/>}
          </ListFrameContents>
      </ListFrameContainer>
    );
  }
}
