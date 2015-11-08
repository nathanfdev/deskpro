import React, { PropTypes } from 'react';
import { CalendarCellDropdownCard } from './CalendarCellDropdownCard';

export class CalendarCellDropdown extends React.Component {

  static propTypes = {
    tasks: PropTypes.object.isRequired
  };

  render() {
    return (
      <div className="dpw--popup-main dpw--popup-main-pointer dpw--popup-main-pointer-top-left calendar-popup">
        <div className="dpw--popup-header">Tasks for</div>

        <div className="dpw--popup-content">
          <div className="dpw--popup-content-line">
            <div className="dpw--popup-content-full">
              <div className="dpwd-calendar-tasks dpwd-calendar-tasks-flat">
                <ul>
                  {this.props.tasks.map(task => <CalendarCellDropdownCard key={task.get('id')} task={task} />)}
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    );
  }
}
