import React, { PropTypes } from 'react';
import { KanbanCheckbox } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/View/Kanban';
import {
  BaseTaskCard,
  Title,
  DateDue,
  SubTasks,
  Comments,
  AssigneeName
} from '../../../TaskCard';
import classNames from 'classnames';

export class TaskCard extends BaseTaskCard {

  static propTypes = {
    className: PropTypes.string,
    moving: PropTypes.bool,
    dragging: PropTypes.bool
  };

  render() {
    const { task, selected, moving, dragging } = this.props;
    const { onToggleSelected, onChange } = this.props;

    return (
      <div className={classNames(
        'card',
        'task-card',
        {
          'moving': moving,
          'dragging-item': dragging
        }
      )}>

        <div className="card-status-bar status-bar-left" />
        <div className="card-status-bar status-bar-right" />

        <KanbanCheckbox selected={selected} onClick={onToggleSelected} />

        <div className="content">
          <Title value={task.get('title')}
                 isDone={task.get('is_done')}
                 onSubmit={onChange.bind(null, 'title')} />

          <div className="card-line task-details">
            <div className="top-right-box">
                <span className="assignment">
                  <AssigneeName task={task} />
                </span>
            </div>
            <div>
              <DateDue value={task.get('date_due')}
                       onChange={onChange.bind(null, 'date_due')} />
            </div>
          </div>
          <hr/>
          <div className="card-line task-properties">
            <Comments count={this.state.comments} />
            {task.get('subtasks_total') > 0 &&
              <SubTasks current={task.get('subtasks_done')}
                        total={task.get('subtasks_total')} />
            }
          </div>
        </div>
      </div>
    );
  }
}
