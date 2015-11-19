import React, { PropTypes } from 'react';
import { TaskCard } from '../TaskCard/TaskCard';
import { TaskDragCard } from '../TaskCard/TaskDragCard';
import Detached from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ClickOut';
import classNames from 'classnames';
import moment from 'moment';

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

  componentWillUnmount() {
    this.isUnmounted = true;
  }

  onOpenTaskCard = event => {
    event.preventDefault();
    this.setState({
      taskCardOpened: true
    });
  };

  onCloseTaskCard = () => {
    if (this.isUnmounted) {
      return;
    }

    this.setState({
      taskCardOpened: false
    });
  };

  render() {
    const { task } = this.props;

    return (
      <li className={classNames(
        {'urgent': moment(task.get('date_due')).isBefore(moment(), 'day')}
      )}>

        <TaskDragCard task={task} onOpenTaskCard={this.onOpenTaskCard} />

        <Detached isOpen={this.state.taskCardOpened}
                  positionTarget={this.refs.button}
                  positionAt="right top-5"
                  zIndex={1001}>

          <ClickOut onClickOut={this.onCloseTaskCard}
                    additionalNodes={[this.refs.button, '.assign-form']}>

            <TaskCard task={task} />
          </ClickOut>
        </Detached>
      </li>
    );
  }
}
