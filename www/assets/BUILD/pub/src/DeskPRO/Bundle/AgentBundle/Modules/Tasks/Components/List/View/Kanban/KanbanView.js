import PropTypes from 'prop-types';
import React from 'react';
import Immutable from 'immutable';
import { ListGroup } from './ListGroup';
import { TaskCardPreviewContainer } from '../../TaskCard/TaskCardPreviewContainer';
import { TaskCardPreview } from './TaskCard/TaskCardPreview';
import { CustomCardDragLayer } from '../../CustomCardDragLayer';

export class KanbanView extends React.Component {

  static propTypes = {
    taskGroups:    PropTypes.object,
    onChangeGroup: PropTypes.func
  };

  constructor(props) {
    super(props);
    const groups = props.taskGroups || Immutable.fromJS([]);
    this.state = {
      groups: groups.filter(taskGroup => taskGroup.get('elements').size)
    };
  }

  componentWillReceiveProps(props) {
    const groups = props.taskGroups || Immutable.fromJS([]);
    this.setState({
      groups: groups.filter(taskGroup => taskGroup.get('elements').size)
    });
  }

  shouldComponentUpdate(props, state) {
    return !Immutable.is(this.state.groups, state.groups);
  }

  onUpdate(index, elements) {
    let group = this.state.groups.get(index);
    group = group.set('elements', elements);
    this.setState({
      groups: this.state.groups.set(index, group)
    });
  }

  render() {
    const { onChangeGroup } = this.props;
    const { groups } = this.state;

    return (
      <div className="kanban kanban-columns">
        {groups.map((taskGroup, index) =>
          <ListGroup
            key={index}
            group={taskGroup}
            onChangeGroup={onChangeGroup}
            onUpdate={this.onUpdate.bind(this, index)}
          />
        )}

        <CustomCardDragLayer>
          <TaskCardPreviewContainer>
            <TaskCardPreview />
          </TaskCardPreviewContainer>
        </CustomCardDragLayer>
      </div>
    );
  }
}
