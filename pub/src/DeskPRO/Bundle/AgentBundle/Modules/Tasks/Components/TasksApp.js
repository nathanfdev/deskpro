import React from 'react';
import { connect } from 'react-redux';
import { AppContainer } from 'DeskPRO/Component/AppContainer';
import HTML5Backend from 'react-dnd-html5-backend';
import { DragDropContext } from 'react-dnd';
import TaskCardDragLayer from './TaskCardDragLayer';

import TasksNavFrame from './TasksNavFrame';
import { TasksListFrame } from './TasksListFrame';
import { meSelector } from 'DeskPRO/Bundle/AgentBundle/Modules/Application/RecordStores/Selectors/meSelectors';

@connect(state => ({
  user: meSelector(state),
  dpWindow: state.Application.dpWindow
}))
class TasksApp extends React.Component {
  render() {
    return (
      <AppContainer thisAppId="tasks" {...this.props}>
        <TasksNavFrame {...this.props} />
        <TasksListFrame {...this.props} />
        <TaskCardDragLayer />
      </AppContainer>
    );
  }
}

export default DragDropContext(HTML5Backend)(TasksApp);
