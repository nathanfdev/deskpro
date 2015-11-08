import React, { PropTypes } from 'react';
import { CalendarCellDropdownCard } from './CalendarCellDropdownCard';

export class CalendarCellDropdown extends React.Component {

  render() {
    return (
      <div className="dpw--popup-main dpw--popup-main-pointer dpw--popup-main-pointer-top-left calendar-popup">
        <div className="dpw--popup-header">Tasks for</div>

        <div className="dpw--popup-content">
          <div className="dpw--popup-content-line">
            <div className="dpw--popup-content-full">
              <div className="dpwd-calendar-tasks dpwd-calendar-tasks-flat">
                <ul>
                  <CalendarCellDropdownCard />
                  <CalendarCellDropdownCard />
                  <CalendarCellDropdownCard />
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    );
  }
}
