import PropTypes from 'prop-types';
import React from 'react';
import { hasErrors, FieldErrors } from 'DeskPRO/Component/Form/FormErrors';
import classNames from 'classnames';

export class FormItem extends React.Component {

  static propTypes = {
    label:    PropTypes.string,
    field:    PropTypes.string,
    errors:   PropTypes.object,
    children: PropTypes.any
  };

  render() {
    const { label, field, children, errors } = this.props;

    return (
      <label className={classNames('inline-form-item', { 'error-field': hasErrors(errors, field) })}>
        <span className="dpdesignportal-form-item-label-title">{label}:</span>

        {children}
        <FieldErrors errors={errors} name={field} />
      </label>
    );
  }
}
