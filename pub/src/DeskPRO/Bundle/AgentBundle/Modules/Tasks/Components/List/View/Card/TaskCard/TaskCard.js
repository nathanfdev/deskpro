import React, { PropTypes } from 'react';
import { MarkDoneButton } from './MarkDoneButton';
import {
  Card,
  CardCheckbox,
  CardLine,
  CardLineLeft,
  CardLineRight
} from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame/Card';
import {
  BaseTaskCard,
  Title,
  DateDue,
  SubTasks,
  Comments,
  ShowDetailsButton,
  AssignButton,
  TicketLinkContainer,
  ProjectContainer,
  CardProject
} from '../../../TaskCard/index';

export class TaskCard extends BaseTaskCard {

  static propTypes = {
    selected: PropTypes.bool,
    onToggleSelected: PropTypes.func,
    task: PropTypes.object,
    currentSort: PropTypes.string,
    connectDragSource: PropTypes.func.isRequired,
    connectDropTarget: PropTypes.func.isRequired,
    isOver: PropTypes.bool,
    isDragging: PropTypes.bool,
    moving: PropTypes.bool
  };

  onToggleDone = () => {
    this.setState({
      isDone: !this.state.isDone
    });
  };

  renderDetails() {
    const { task } = this.props;

    return (
      <CardLine>
        <CardLineLeft>
          <DateDue value={task.get('date_due')}
                   onChange={this.onChangeDate} />

          {task.get('project') &&
            <ProjectContainer project={task.get('project')}>
              <CardProject />
            </ProjectContainer>
          }
          {this.state.ticketLink && <TicketLinkContainer ticket={this.state.ticketLink} />}
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
    const { task, moving, selected, onToggleSelected } = this.props;

    return (
      <Card moving={moving} minimized={this.isMinimized()} type="task">
        <MarkDoneButton isDone={task.get('is_done')}
                        onToggle={this.onToggleDone} />

        <CardCheckbox selected={selected} onClick={onToggleSelected} />
        <CardLine>
          <CardLineLeft>
            <Title value={task.get('title')}
                   isDone={task.get('is_done')}
                   onChange={this.onTitleChange} />
          </CardLineLeft>
          <CardLineRight>
            {task.get('is_done')
              ? <ShowDetailsButton expanded={this.state.expanded}
                                   onToggleExpand={this.onToggleExpand}/>
              : <AssignButton task={task} />
            }
          </CardLineRight>
        </CardLine>

        {!this.isMinimized() && this.renderDetails()}
      </Card>
    );
  }
}
