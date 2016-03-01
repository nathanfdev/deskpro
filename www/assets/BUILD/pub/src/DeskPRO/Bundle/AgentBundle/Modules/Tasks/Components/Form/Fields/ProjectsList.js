import React, { PropTypes } from 'react';
import { BaseList } from './BaseList';

export class ProjectsList extends BaseList {

  renderLabel(value) {
    return (
      value.get('title')
    );
  }

  getKeyword(value) {
    return value.get('title');
  }
}
