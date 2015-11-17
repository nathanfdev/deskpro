import React, { PropTypes } from 'react';
import { DragSource } from 'react-dnd';
import { cardSpec, cardCollect } from '../TaskCardContainer';
import { KanbanCheckbox } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/Kanban/index';
import {
  BaseTaskCard,
  Title,
  DateDue,
  SubTasks,
  Comments
} from '../../TaskCard/index';

@DragSource('TASK', cardSpec, cardCollect)
export class TaskCard extends BaseTaskCard {

  static propTypes = {
    selected: PropTypes.bool,
    onToggleSelected: PropTypes.func,
    task: PropTypes.object,
    connectDragSource: PropTypes.func
  };

  render() {
    const { selected, onToggleSelected, connectDragSource } = this.props;

    return connectDragSource(
      <div>
        <div className="card task-card">
          <div className="card-status-bar status-bar-left" />
          <div className="card-status-bar status-bar-right" />

          <KanbanCheckbox selected={selected} onClick={onToggleSelected} />

          <div className="content">
            <Title value={this.state.title}
                   isDone={this.state.isDone}
                   onChange={this.onTitleChange} />

            <div className="card-line task-details">
              <div className="top-right-box">
                  <span className="assignment">
                    assigneeName
                  </span>
              </div>
              <div>
                <DateDue value={this.state.dateDue}
                         onChange={this.onChangeDate} />
              </div>
            </div>
            <hr/>
            <div className="card-line task-properties">
              <Comments count={this.state.comments} />
              {this.state.subTasks.total > 0 &&
                <SubTasks current={this.state.subTasks.current}
                          total={this.state.subTasks.total} />
              }
            </div>
          </div>
        </div>
      </div>
    );
  }
}
