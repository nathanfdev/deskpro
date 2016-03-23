import React, { PropTypes } from 'react';
import {
  Card,
  CardLine,
  CardLineLeft,
  CardLineRight
} from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/View/Card';
import {
  BaseTaskCard,
  Title,
  DateDue,
  SubTasks,
  Comments,
  AssignButton,
  LinkedItem,
  CardProjectContainer
} from '../../../TaskCard';

export class TaskCard extends BaseTaskCard {

  static propTypes = {
    moving: PropTypes.bool
  };

  renderDetails() {
    const { task, onChange } = this.props;

    return (
      <CardLine>
        <CardLineLeft>
          <DateDue value={task.get('date_due')}
                   onChange={onChange.bind(null, 'date_due')} />
          <CardProjectContainer value={task.get('project')}
                                onChange={onChange.bind(null, 'project')} />
          <LinkedItem value={task} />
        </CardLineLeft>
        <CardLineRight>
          <Comments count={this.state.comments} />
          {task.get('subtasks_total') > 0 &&
            <SubTasks current={task.get('subtasks_done')}
                      total={task.get('subtasks_total')} />
          }
        </CardLineRight>
      </CardLine>
    );
  }

  render() {
    const { task, moving, onChange } = this.props;

    return (
      <Card statusBars={false}
            type="task"
            moving={moving}
            additionalClasses="calendar-task-card">

        <CardLine>
          <CardLineLeft>
            <Title value={task.get('title')}
                   isDone={task.get('is_done')}
                   onSubmit={onChange.bind(null, 'title')} />
          </CardLineLeft>
          <CardLineRight>
            {!task.get('is_done') && <AssignButton value={task} onChange={onChange.bind(null, 'assignee')} />}
          </CardLineRight>
        </CardLine>

        {this.renderDetails()}
      </Card>
    );
  }
}
