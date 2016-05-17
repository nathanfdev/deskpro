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
    const { task } = this.props;
    const onChange = this.onChange;

    return (
      <div className="details-line">
        <div>
          <div className="dpwd--card-line-item-container">
            <DateDue value={task.get('date_due')} onChange={val => onChange('date_due', val)} />
          </div>
          <div className="dpwd--card-line-item-container">
            <CardProjectContainer value={task.get('project')} onChange={val => onChange('project', val)} />
          </div>
          <div className="dpwd--card-line-item-container">
            <LinkedItemContainer value={task} onChange={val => onChange('linked_items', val)} />
          </div>
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
    const { task, moving } = this.props;
    const onChange = this.onChange;

    return (
      <Card statusBars={false} type="task" moving={moving} additionalClasses="calendar-task-card">

        <div className="title-line">
          <Title value={task.get('title')} isDone={task.get('is_done')} onSubmit={val => onChange('title', val)} />
          <div className="icon-block">
            {!task.get('is_done') && <AssignButton value={task} onChange={val => onChange('assignee', val)} />}
          </div>
        </div>

        {this.renderDetails()}
      </Card>
    );
  }
}
