import React, { PropTypes } from 'react';
import { BaseList } from './BaseList';

export class DepartmentsList extends BaseList {

  renderLabel(value) {
    return (
      value.get('title')
    );
  }

  getKeyword(value) {
    return value.get('title');
  }
}
