import React, { PropTypes } from 'react';
import { TaskCard } from '../TaskCard/TaskCard';
import Detached from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ClickOut';

export class CalendarCellContentItem extends React.Component {

  static propTypes = {
    task: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);
    this.state = {
      taskCardOpened: false
    };
  }

  onOpenTaskCard = event => {
    event.preventDefault();
    this.setState({
      taskCardOpened: true
    });
  };

  onCloseTaskCard = () => {
    this.setState({
      taskCardOpened: false
    });
  };

  render() {
    const { task } = this.props;

    return (
      <li>
        <a href="#"
           ref="button"
           onClick={this.onOpenTaskCard}>

          {task.get('title')}
        </a>

        <Detached isOpen={this.state.taskCardOpened}
                  positionTarget={this.refs.button}>

          <ClickOut onClickOut={this.onCloseTaskCard}
                    ignoreNodes={[this.refs.button]}>

            <TaskCard task={task} />
          </ClickOut>
        </Detached>
      </li>
    );
  }
}
