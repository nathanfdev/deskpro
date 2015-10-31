import React from 'react';
import { ListFrameContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import ListFrameContents from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrameContents';
import { Controls } from './ControlBar/Controls';
import { TaskForm } from './TaskForm';

export class List extends React.Component {

  render() {
    return (
      <ListFrameContainer>
        <Controls />
        <ListFrameContents>
          <TaskForm />
        </ListFrameContents>
      </ListFrameContainer>
    );
  }
}
