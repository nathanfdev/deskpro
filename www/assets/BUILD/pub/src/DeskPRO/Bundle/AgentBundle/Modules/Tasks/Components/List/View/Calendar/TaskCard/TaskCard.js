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
  LinkedItemContainer,
  CardProjectContainer
} from '../../../TaskCard/index';

export class TaskCard extends BaseTaskCard {

  static propTypes = {
    moving: PropTypes.bool
  };

  renderDetails() {
    const { task, onChange } = this.props;

    return (
      <CardLine>
        <CardLineLeft>
          <DateDue value={task.get('date_due')} onChange={value => onChange('date_due', value)} />
          <CardProjectContainer value={task.get('project')} onChange={value => onChange('project', value)} />
          <LinkedItemContainer value={task} onChange={value => onChange('linked_items', value)} />
        </CardLineLeft>
        <CardLineRight>
          <Comments count={this.state.comments} />
          {task.get('subtasks_total') > 0 &&
            <SubTasks current={task.get('subtasks_done')} total={task.get('subtasks_total')} />
          }
        </CardLineRight>
      </CardLine>
    );
  }

  render() {
    const { task, moving, onChange } = this.props;

    return (
      <Card
        statusBars={false}
        type="task"
        moving={moving}
        additionalClasses="calendar-task-card"
        >

        <CardLine>
          <CardLineLeft>
            <Title
              value={task.get('title')}
              isDone={task.get('is_done')}
              onSubmit={() => onChange('title')}
              />
          </CardLineLeft>
          <CardLineRight>
            {!task.get('is_done') && <AssignButton value={task} onChange={value => onChange('assignee', value)} />}
          </CardLineRight>
        </CardLine>

        {this.renderDetails()}
      </Card>
    );
  }
}
