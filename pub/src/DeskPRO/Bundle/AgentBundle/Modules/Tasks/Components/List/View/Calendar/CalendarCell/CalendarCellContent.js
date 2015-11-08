import React, { PropTypes } from 'react';
import { CalendarCellDropdown } from './CalendarCellDropdown';
import Detached from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/Positioned/Detached';
import { ClickOut } from 'DeskPRO/Bundle/AgentBundle/Modules/Common/Components/ClickOut';

export class CalendarCellContent extends React.Component {

  static propTypes = {
    dayDate: PropTypes.object.isRequired,
    tasks: PropTypes.object.isRequired
  };

  render() {
    return (
      <div>
        <ul>
          {this.props.tasks.map(task =>
            <li key={task.get('id')}>
              {task.get('title')}
            </li>
          )}
          <li>
            <a href="#" className="dpwd-calendar-tasks-show-more">
              + 10 tasks <i className="fa fa-sort" />
            </a>
          </li>
        </ul>

        <Detached>
          <ClickOut>
            <CalendarCellDropdown />
          </ClickOut>
        </Detached>
      </div>
    );
  }
}
