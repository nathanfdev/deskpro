import React from 'react';

import { ListFrame } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';
import { ChatsListControlBar } from './ControlBar/ChatsListControlBar';
import { ChatsList } from './View/List/ChatsList';
import { ChatsTable } from './View/Table/ChatsTable';

export class List extends React.Component {
  render() {
    const { elements, viewMode } = this.props;

    return (
      <ListFrame>
        <ChatsListControlBar />
        {viewMode === 'list' ? <ChatsList elements={elements} /> : <ChatsTable elements={elements} />}
      </ListFrame>
    );
  }
}
