import React, { PropTypes } from 'react';
import classNames from 'classnames';

export class FormItem extends React.Component {

  static propTypes = {
    label: PropTypes.string,
    error: PropTypes.bool,
    children: PropTypes.any
  };

  render() {
    const { label, children, error } = this.props;

    return (
      <label className={classNames('inline-form-item', {'error': error})}>
        <span className="dpdesignportal-form-item-label-title">{label}:</span>
        {children}
      </label>
    );
  }
}
