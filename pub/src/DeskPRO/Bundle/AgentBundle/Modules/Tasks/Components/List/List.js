import React from 'react';
import { ListFrameContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import ListFrameContents from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrameContents';
import { Controls } from './ControlBar/Controls';
import { TaskForm } from './TaskForm';
import { TaskCard } from './View/List/TaskCard/TaskCard';
import Immutable from 'immutable';

export class List extends React.Component {

  render() {
    return (
      <ListFrameContainer>
        <Controls />
        <ListFrameContents>
          <TaskForm />
          <TaskCard task={Immutable.fromJS({date_due: '2015-01-01'})} />
          <TaskCard task={Immutable.fromJS({date_due: '2016-01-01'})} />
          <TaskCard task={Immutable.fromJS({})} />
          <TaskCard task={Immutable.fromJS({})} />

        </ListFrameContents>
      </ListFrameContainer>
    );
  }
}
