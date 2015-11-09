import React from 'react';

export class BaseTaskCard extends React.Component {

  constructor(props) {
    super(props);

    this.state = {
      expanded: false,
      selected: false,
      title: props.task.get('title'),
      dateDue: props.task.get('date_due'),
      project: 'Some Project',
      ticketLink: 'Some Ticket',
      comments: 1,
      subTasks: {
        current: props.task.get('subtasks_done'),
        total: props.task.get('subtasks_total')
      },
      isDone: props.task.get('is_done')
    };
  }

  onToggleSelect = () => {
    this.setState({
      selected: !this.state.selected
    });
  };

  onTitleChange = value => {
    this.setState({
      title: value
    });
  };

  onChangeDate = value => {
    this.setState({
      dateDue: value
    });
  };
}
