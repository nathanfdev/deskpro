import React, { PropTypes } from 'react';
import { HeaderContainer } from './HeaderContainer';
import { TaskCardPreviewContainer } from '../../TaskCard/TaskCardPreviewContainer';
import { TaskCardPreview } from '../Card/TaskCard/TaskCardPreview';
import { ListGroup } from './ListGroup';
import { Table } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ListFrame';
import { CustomCardDragLayer } from '../../CustomCardDragLayer';
import Immutable from 'immutable';

export class TableView extends React.Component {

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

  onUpdate = (index, elements) => {
    let group = this.state.groups.get(index);
    group = group.set('elements', elements);
    this.setState({
      groups: this.state.groups.set(index, group)
    });
  };

  render() {
    const { onChangeGroup } = this.props;
    const { groups } = this.state;

    return (
      <div>
        <Table tableClassName="fixed-layout">
          <HeaderContainer />

          {groups.map((taskGroup, index) =>
            <ListGroup key={index}
              group={taskGroup}
              onChangeGroup={onChangeGroup}
              onUpdate={els => this.onUpdate(index, els)}
            />
          )}
        </Table>

        <CustomCardDragLayer>
          <TaskCardPreviewContainer>
            <TaskCardPreview />
          </TaskCardPreviewContainer>
        </CustomCardDragLayer>
      </div>
    );
  }
}
