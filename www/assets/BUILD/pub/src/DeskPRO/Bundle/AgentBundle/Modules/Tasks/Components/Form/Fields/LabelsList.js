import React, { PropTypes } from 'react';
import { BaseList } from './BaseList';

export class LabelsList extends React.Component {

  renderLabel(value) {
    return value.get('label');
  }

  getKeyword(value) {
    return value.get('label');
  }
}
