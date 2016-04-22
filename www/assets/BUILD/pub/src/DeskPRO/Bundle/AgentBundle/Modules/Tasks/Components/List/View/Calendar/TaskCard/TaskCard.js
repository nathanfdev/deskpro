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
      <div className="details-line">
        <div>
          <DateDue value={task.get('date_due')} onChange={onChange.bind(null, 'date_due')} />
          <CardProjectContainer value={task.get('project')} onChange={onChange.bind(null, 'project')} />
          <LinkedItemContainer value={task} onChange={onChange.bind(null, 'linked_items')} />
        </div>
        <div className="icon-block">
          <Comments count={this.state.comments} />
          {task.get('subtasks_total') > 0 &&
            <SubTasks current={task.get('subtasks_done')} total={task.get('subtasks_total')} />
          }
        </div>
      </div>
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

        <div className="title-line">
          <Title value={task.get('title')} isDone={task.get('is_done')} onSubmit={onChange.bind(null, 'title')} />
          <div className="icon-block">
            {!task.get('is_done') && <AssignButton value={task} onChange={onChange.bind(null, 'assignee')} />}
          </div>
        </div>

        {this.renderDetails()}
      </Card>
    );
  }
}
