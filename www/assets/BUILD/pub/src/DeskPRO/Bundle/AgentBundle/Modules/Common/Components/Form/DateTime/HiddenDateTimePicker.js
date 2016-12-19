import React from 'react';
import { AbstractDateTimePicker } from './AbstractDateTimePicker';

export class HiddenDateTimePicker extends AbstractDateTimePicker {

  componentDidMount() {
    super.componentDidMount();
    this.picker.show();
  }

  render() {
    return (
      <div style={{ position: 'relative' }}>
        <div ref="anchor">
          <input type="hidden" ref="input" />
        </div>
      </div>
    );
  }
}
