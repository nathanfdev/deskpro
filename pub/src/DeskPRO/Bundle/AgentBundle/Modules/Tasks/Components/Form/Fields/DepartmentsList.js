import React, { PropTypes } from 'react';
import { CheckboxList } from './CheckboxList';
import { BaseList } from './BaseList';

export class DepartmentsList extends BaseList {

  renderLabel(value) {
    return (
      value.get('title')
    );
  }

}
