import React, { PropTypes } from 'react';
import { Year } from './Year';
import { Month } from './Month';

export class Controls extends React.Component {

  render() {
    return (
      <div className="dpwd-calendar-controls">
        <Year date="2015" />
        <Month />
      </div>
    );
  }
}
