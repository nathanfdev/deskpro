import React, { PropTypes } from 'react';
import { hasErrors, FieldErrors } from 'DeskPRO/Component/Form/FormErrors';
import classNames from 'classnames';

export class FormItem extends React.Component {

  static propTypes = {
    label: PropTypes.string,
    field: PropTypes.string,
    errors: PropTypes.object,
    children: PropTypes.any
  };

  render() {
    const { label, field, children, errors } = this.props;

    return (
      <div className={classNames({'error': hasErrors(errors, field)})}>
        <label>{label}</label>

        {children}
        <FieldErrors errors={errors} name={field} />
      </div>
    );
  }
}
