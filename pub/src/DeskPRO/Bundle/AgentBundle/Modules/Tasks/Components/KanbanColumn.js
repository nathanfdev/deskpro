import React, {PropTypes} from "react";
import TaskKanbanCard from "./TaskKanbanCard";
import { connect } from 'redux/react';
import * as TaskActions from "../Actions/TaskListActions";
import { DropTarget } from 'react-dnd';
import DragTypes from "../../../Services/DragTypes.js";

function collect(connect, monitor) {
  return {
    connectDropTarget: connect.dropTarget(),
    isOver: monitor.isOver()
  };
}

const listTarget = {
  drop(props, monitor) {
    const item = monitor.getItem();

    if (item[props.updateField] !== props.updateValue) {
      let update = {
        taskId : item.id
      };
      update[props.updateField] = props.updateValue;

      item.dispatch(TaskActions.editTask(update, 'tasks'));
    }
  }
};

@DropTarget(DragTypes.TASK, listTarget, collect)
export default class KanbanColumn extends React.Component {
  constructor(props) {
    super(props);
    this.state = {
      tasks: props.tasks
    }
  }

  moveCard(item, targetItem) {
    const cards = this.state.tasks;
    const id = item.id;
    const afterId = targetItem.id;

    let oldOrder = [];
    this.state.tasks.forEach((card) => {
      oldOrder.push(card.display_order);
    });

    const card = cards.filter(c => c.id === id)[0];
    const afterCard = cards.filter(c => c.id === afterId)[0];
    const cardIndex = cards.indexOf(card);
    const afterIndex = cards.indexOf(afterCard);

    cards.splice(cardIndex, 1);
    cards.splice(afterIndex, 0, card);

    this.setState({
      tasks: cards
    });

    this.props.editTask(
      this.props.source,
      {
        taskId: card.id,
        display_order: targetItem.display_order
      }
    );
  }

  render() {
    const _this = this;

    const columnClass = this.props.isOver ? 'list drag-hover' : 'list';

    return this.props.connectDropTarget(<div className={columnClass}>
      <h1 className="kanban-list-header">{this.props.taskList.title}</h1>
      {
        this.state.tasks ? this.state.tasks.map((task) => {
          return <TaskKanbanCard task={task} key={task.id} departments={this.props.departments}
                               agents={this.props.agents} teams={this.props.teams}
                               dispatch={_this.props.dispatch.bind(_this)}
                               moveCard={this.moveCard.bind(this)}
                              />
        }) : ''
      }
    </div>);
  }
}
