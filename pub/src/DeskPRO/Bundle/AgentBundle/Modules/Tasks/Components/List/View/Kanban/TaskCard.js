import React, { PropTypes } from 'react';
import { KanbanCheckbox } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/Kanban/index';
import {
  BaseTaskCard,
  Title,
  DateDue,
  SubTasks,
  Comments
} from '../../TaskCard/index';
import classNames from 'classnames';

export class TaskCard extends BaseTaskCard {

  static propTypes = {
    selected: PropTypes.bool,
    onToggleSelected: PropTypes.func,
    task: PropTypes.object,
    moving: PropTypes.bool
  };

  render() {
    const { task, selected, onToggleSelected, moving } = this.props;

    return (
        <div className={classNames(
          'card',
          'task-card',
          {'moving': moving}
        )}>

          <div className="card-status-bar status-bar-left" />
          <div className="card-status-bar status-bar-right" />

          <KanbanCheckbox selected={selected} onClick={onToggleSelected} />

          <div className="content">
            <Title value={task.get('title')}
                   isDone={task.get('is_done')}
                   onChange={this.onTitleChange} />

            <div className="card-line task-details">
              <div className="top-right-box">
                  <span className="assignment">
                    assigneeName
                  </span>
              </div>
              <div>
                <DateDue value={task.get('date_due')}
                         onChange={this.onChangeDate} />
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
