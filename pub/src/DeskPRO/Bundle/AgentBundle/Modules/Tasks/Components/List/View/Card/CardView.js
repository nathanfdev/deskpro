import React, { PropTypes } from 'react';
import ListFrameContents from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrameContents';
import { ListGroup } from './ListGroup';
import { TaskCard } from './TaskCard/TaskCard';
import Immutable from 'immutable';

export class CardView extends React.Component {

  static propTypes = {
    tasks: PropTypes.object.isRequired
  };

  render() {
    return (
      <ListFrameContents>
        <ListGroup title="Overdue">
          {this.props.tasks.map((task, index) => <TaskCard task={task} key={index} />)}
        </ListGroup>
        <ListGroup title="Other">
          <TaskCard task={Immutable.fromJS({date_due: '2015-11-04 20:00'})} />
          <TaskCard task={Immutable.fromJS({})} />
        </ListGroup>
      </ListFrameContents>
    );
  }
}
