import React, { PropTypes } from 'react';
import { DragSource, DropTarget } from 'react-dnd';
import { cardSourceSpec, cardSourceCollect, cardTargetSpec, targetCollect } from '../TaskCardContainer';
import { KanbanCheckbox } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/Kanban/index';
import {
  BaseTaskCard,
  Title,
  DateDue,
  SubTasks,
  Comments
} from '../../TaskCard/index';

@DragSource('TASK', cardSourceSpec, cardSourceCollect)
@DropTarget('TASK', cardTargetSpec, targetCollect)
export class TaskCard extends BaseTaskCard {

  static propTypes = {
    selected: PropTypes.bool,
    onToggleSelected: PropTypes.func,
    task: PropTypes.object,
    currentSort: PropTypes.string,
    connectDragSource: PropTypes.func.isRequired,
    connectDropTarget: PropTypes.func.isRequired
  };

  render() {
    const { selected, onToggleSelected, currentSort } = this.props;
    const { connectDragSource, connectDropTarget } = this.props;

    let result = connectDragSource(
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

    if (currentSort === 'list') {
      result = connectDropTarget(result);
    }

    return result;
  }
}
