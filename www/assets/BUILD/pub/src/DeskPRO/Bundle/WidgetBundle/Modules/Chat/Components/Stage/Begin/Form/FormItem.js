import React, { PropTypes } from 'react';
import { hasErrors, FieldErrors } from 'DeskPRO/Component/Form/FormErrors';
import classNames from 'classnames';

export class FormItem extends React.Component {

  static propTypes = {
    className: PropTypes.string,
    label:     PropTypes.string,
    field:     PropTypes.string,
    errors:    PropTypes.object,
    children:  PropTypes.any
  };

  render() {
    const { className, label, field, children, errors } = this.props;

    return (
      <div className={classNames(className, { 'error-field': hasErrors(errors, field) })}>
        <label>{label}</label>

        {children}
        <FieldErrors errors={errors} name={field} />
      </div>
    );
  }
}
