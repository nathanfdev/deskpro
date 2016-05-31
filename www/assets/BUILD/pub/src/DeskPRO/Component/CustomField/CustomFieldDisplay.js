import React from 'react';
import { AbstractCustomField } from './AbstractCustomField';

export class CustomFieldDisplay extends AbstractCustomField {

  render() {
    return (
      <div className="form-display">
        {this.props.config.getIn(['options', 'html'])}
      </div>
    );
  }
}
