import React, { PropTypes } from 'react';
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
    const classes = 'dpw--popup-item-person ' + (this.state.checked ? 'active' : '');

    return (
      <div className={classes}>
        <span className="dpw--avatar-face"></span>
        <span className="dpw-popup-item-collection-name">
          {value.get('title')}
        </span>
      </div>
    );
  }
}
