import React, { PropTypes } from 'react';
import { VIEW_MODE_LIST } from 'DeskPRO/Bundle/AgentBundle/Constants/Constants';
import { ListFrameContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import { ChatsListControlBar } from './ControlBar/ChatsListControlBar';
import { ChatsList } from './View/List/ChatsList';
import { ChatsTable } from './View/Table/ChatsTable';

export class List extends React.Component {
  static propTypes = {
    elements: PropTypes.array.isRequired,
    viewMode: PropTypes.string.isRequired
  };

  render() {
    const { elements, viewMode } = this.props;

    return (
      <ListFrameContainer>
        <ChatsListControlBar />
        {viewMode === VIEW_MODE_LIST ? <ChatsList elements={elements} /> : <ChatsTable elements={elements} />}
      </ListFrameContainer>
    );
  }
}
