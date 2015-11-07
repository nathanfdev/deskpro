import React, { PropTypes } from 'react';
import { ListFrameContainer } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/index';
import ListFrameContents from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrameContents';
import { Controls } from './ControlBar/Controls';
import { ListGroup } from './ListGroup';
import { TaskCard } from './TaskCard/TaskCard';
import Immutable from 'immutable';
import Loader from 'react-loader';

export class List extends React.Component {

  static propTypes = {
    tasks: PropTypes.array.isRequired,
    loaded: PropTypes.bool.isRequired
  };

  render() {
    const { tasks, loaded } = this.props;

    return (
      <ListFrameContainer>
        <Controls />

        <Loader loaded={loaded}>
          <ListFrameContents>
            <ListGroup title="Overdue">
              {tasks.map((task, index) => <TaskCard task={task} key={index} />)}
            </ListGroup>
            <ListGroup title="Other">
              <TaskCard task={Immutable.fromJS({date_due: '2015-11-04 20:00'})} />
              <TaskCard task={Immutable.fromJS({})} />
            </ListGroup>
          </ListFrameContents>
        </Loader>
      </ListFrameContainer>
    );
  }
}
