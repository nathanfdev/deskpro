import React, { PropTypes } from 'react';
import { CalendarCellDropdownItem } from './CalendarCellDropdownItem';

export class CalendarCellDropdown extends React.Component {

  render() {
    return (
      <div>
        <CalendarCellDropdownItem />
        <CalendarCellDropdownItem />
        <CalendarCellDropdownItem />
      </div>
    );
  }
}
