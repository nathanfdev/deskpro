import React from 'react';
import { Year } from './Year';
import { Month } from './Month';

export class Controls extends React.Component {

  render() {
    return (
      <div className="dpwd-calendar-controls">
        <Year {...this.props} />
        <Month {...this.props} />
      </div>
    );
  }
}
