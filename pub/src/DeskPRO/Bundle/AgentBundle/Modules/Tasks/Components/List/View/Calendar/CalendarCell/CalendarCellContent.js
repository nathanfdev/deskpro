import React, { PropTypes } from 'react';
import { CalendarCellDropdown } from './CalendarCellDropdown';
import Detached from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ClickOut';

export class CalendarCellContent extends React.Component {

  static propTypes = {
    dayDate: PropTypes.object.isRequired,
    tasks: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);

    this.state = {
      dropdownOpened: false
    };
  }

  onOpenDropdown = event => {
    event.preventDefault();
    this.setState({
      dropdownOpened: true
    });
  };

  onCloseDropdown = () => {
    this.setState({
      dropdownOpened: false
    });
  };

  render() {
    const { tasks } = this.props;
    const shortList = tasks.slice(0, 2);

    return (
      <div>
        <ul>
          {shortList.map(task =>
            <li key={task.get('id')}>
              {task.get('title')}
            </li>
          )}
          <li>
            <a href="#"
               ref="button"
               className="dpwd-calendar-tasks-show-more"
               onClick={this.onOpenDropdown}>

              + 10 tasks <i className="fa fa-sort" />
            </a>
          </li>
        </ul>

        <Detached isOpen={this.state.dropdownOpened}
                  positionTarget={this.refs.button}
                  positionAt="left bottom+5">

          <ClickOut onClickOut={this.onCloseDropdown}>
            <CalendarCellDropdown tasks={tasks} />
          </ClickOut>
        </Detached>
      </div>
    );
  }
}
