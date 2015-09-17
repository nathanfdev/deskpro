import React from 'react';
import * as constants from 'DeskPRO/Bundle/AgentBundle/Constants/Constants'

import { ListFrame } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/Components/ListFrame/index';
import { ChatsListControlBar } from './ControlBar/ChatsListControlBar';
import { ChatsList } from './View/List/ChatsList';
import { ChatsTable } from './View/Table/ChatsTable';

export class List extends React.Component {
  render() {
    const { elements, viewModeOptions } = this.props;

    return (
      <ListFrame>
        <ChatsListControlBar />
        {viewModeOptions.find((option)=>option.current === true).field === constants.VIEW_MODE_LIST ?
         <ChatsList elements={elements}/> : <ChatsTable elements={elements}/>
        }
      </ListFrame>
    );
  }
}
