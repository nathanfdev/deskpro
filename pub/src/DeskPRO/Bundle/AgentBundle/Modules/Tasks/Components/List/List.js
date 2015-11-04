import React from 'react';
import { ListFrameContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import ListFrameContents from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrameContents';
import { Controls } from './ControlBar/Controls';
import { ListGroup } from './ListGroup';
import { TaskCard } from './View/List/TaskCard/TaskCard';
import Immutable from 'immutable';

export class List extends React.Component {

  render() {
    return (
      <ListFrameContainer>
        <Controls />
        <ListFrameContents>
          <ListGroup title="Overdue">
            <TaskCard task={Immutable.fromJS({date_due: '2015-01-01 11:15'})} />
            <TaskCard task={Immutable.fromJS({date_due: '2016-01-01'})} />
          </ListGroup>
          <ListGroup title="Other">
            <TaskCard task={Immutable.fromJS({date_due: '2015-11-04 20:00'})} />
            <TaskCard task={Immutable.fromJS({})} />
          </ListGroup>
        </ListFrameContents>
      </ListFrameContainer>
    );
  }
}
