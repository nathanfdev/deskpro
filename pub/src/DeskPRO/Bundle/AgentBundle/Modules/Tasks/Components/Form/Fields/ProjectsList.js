import React, { PropTypes } from 'react';
import { CheckboxList } from './CheckboxList';
import { BaseList } from './BaseList';

export class ProjectsList extends BaseList {

  renderLabel(value) {
    return (
      value.get('title')
    );
  }

}
