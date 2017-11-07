import PropTypes from 'prop-types';
import React from 'react';
import { RadioListItem } from './RadioListItem';
import Immutable from 'immutable';

export class DepartmentsListItem extends RadioListItem {

  static propTypes = {
    value: PropTypes.object.isRequired
  };

  /**
   * todo avatar components must comply with the current layout
   * @returns {XML}
   */
  render() {
    const { value } = this.props;
    const classes = `dpw--popup-item-person${this.state.checked ? ' active' : ''}`;

    return (
      <div className={classes} title={value.get('title')}>
        <span className="dpw--avatar-face" />
        <span className="dpw-popup-item-collection-name">
          {value.get('title')}
        </span>
      </div>
    );
  }
}
