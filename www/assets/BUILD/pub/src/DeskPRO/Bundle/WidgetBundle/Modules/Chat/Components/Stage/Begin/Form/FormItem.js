import PropTypes from 'prop-types';
import React from 'react';
import { hasErrors, FieldErrors } from 'DeskPRO/Component/Form/FormErrors';
import classNames from 'classnames';

export class FormItem extends React.Component {

  static propTypes = {
    className: PropTypes.string,
    label:     PropTypes.string,
    field:     PropTypes.string,
    errors:    PropTypes.object,
    children:  PropTypes.any, // eslint-disable-line react/forbid-prop-types
    required:  PropTypes.bool
  };

  render() {
    const { className, label, field, children, errors, required } = this.props;

    return (
      <div className={classNames(className, { 'error-field': hasErrors(errors, field) })}>
        <label htmlFor={field}>{label}{required ? ' *' : ''}</label>

        {children}
        <FieldErrors errors={errors} name={field} />
      </div>
    );
  }
}
