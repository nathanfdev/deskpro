import PropTypes from 'prop-types';
import React from 'react';
import { DepartmentsListItem } from './DepartmentsListItem';
import { EntityList } from './EntityList';

export class DepartmentsList extends EntityList {

  constructor(props) {
    super(props);
    this.item = DepartmentsListItem;
  }

  keyword(value) {
    return value.get('title');
  }
}
