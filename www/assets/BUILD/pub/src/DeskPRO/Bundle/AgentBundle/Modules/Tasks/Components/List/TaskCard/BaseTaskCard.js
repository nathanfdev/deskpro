import React, { PropTypes } from 'react';
import { editTask } from 'DeskPRO/Bundle/AgentBundle/Modules/Tasks/Actions/listActions';


export class BaseTaskCard extends React.Component {

  static propTypes = {
    task: PropTypes.object
  };

  constructor(props) {
    super(props);

    this.state = {
      expanded: false,
      ticketLink: 'Some Ticket',
      comments: 1
    };
  }

  onToggleExpand = () => {
    this.setState({
      expanded: !this.state.expanded
    });
  };

  onAssign = (value) => {
    const { dispatch, task } = this.props;
    return dispatch(editTask(task.get('id'), {
      agents: value.get('agents').toArray(),
      teams: value.get('teams').toArray(),
      departments: value.get('departments').toArray()
    }));
  };

  isMinimized() {
    const { task } = this.props;

    return !this.state.expanded && task.get('is_done');
  }
}
