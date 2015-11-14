import React from 'react';
import { AbstractDateTimePicker } from './AbstractDateTimePicker';

export class HiddenDateTimePicker extends AbstractDateTimePicker {

  componentDidMount() {
    super.componentDidMount();
    this.picker.show();
  }

  render() {
    return (
      <div>
        <input type="hidden" ref="input" />
      </div>
    );
  }
}
