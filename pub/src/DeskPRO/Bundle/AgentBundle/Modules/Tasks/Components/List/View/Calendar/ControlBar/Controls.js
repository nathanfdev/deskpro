import React, { PropTypes } from 'react';
import { Year } from './Year';
import { Month } from './Month';

export class Controls extends React.Component {

  render() {
    return (
      <div>
        <Year />
        <Month />
      </div>
    );
  }
}
