import React, { PropTypes } from 'react';
import { DragSource, DropTarget } from 'react-dnd';
import { cardSourceSpec, cardSourceCollect, cardTargetSpec, cardTargetCollect } from '../../TaskCardContainer';
import classNames from 'classnames';
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

@DragSource('TASK', cardSourceSpec, cardSourceCollect)
@DropTarget('TASK', cardTargetSpec, cardTargetCollect)
export class TaskCard extends BaseTaskCard {

  static propTypes = {
    selected: PropTypes.bool,
    onToggleSelected: PropTypes.func,
    task: PropTypes.object,
    connectDragSource: PropTypes.func
  };

  onToggleDone = () => {
    this.setState({
      isDone: !this.state.isDone
    });
  };

  renderDetails() {
    return (
      <CardLine>
        <CardLineLeft>
          <DateDue value={this.state.dateDue}
                   onChange={this.onChangeDate} />

          {this.state.project &&
            <ProjectContainer project={this.state.project}>
              <CardProject />
            </ProjectContainer>
          }
          {this.state.ticketLink && <TicketLinkContainer ticket={this.state.ticketLink} />}
        </CardLineLeft>
        <CardLineRight>
          <Comments count={this.state.comments} />
          {this.state.subTasks.total > 0 &&
            <SubTasks current={this.state.subTasks.current}
                      total={this.state.subTasks.total} />
          }
        </CardLineRight>
      </CardLine>
    );
  }

  render() {
    const { selected, onToggleSelected, connectDragSource, isOver } = this.props;

    return connectDragSource(
      <div>
        <Card minimized={this.isMinimized()} type="task">
          <MarkDoneButton isDone={this.state.isDone}
                          onToggle={this.onToggleDone} />

          <CardCheckbox selected={selected} onClick={onToggleSelected} />
          <CardLine>
            <CardLineLeft>
              <Title value={this.state.title}
                     isDone={this.state.isDone}
                     onChange={this.onTitleChange} />
            </CardLineLeft>
            <CardLineRight>
              {this.state.isDone
                ? <ShowDetailsButton expanded={this.state.expanded}
                                     onToggleExpand={this.onToggleExpand}/>
                : <AssignButton task={this.props.task} />
              }
            </CardLineRight>
          </CardLine>

          {!this.isMinimized() && this.renderDetails()}
        </Card>
        <div className={classNames('placeholder', {'is-over': isOver})} />
      </div>
    );
  }
}
