import PropTypes from 'prop-types';
import React from 'react';
import { UserInfoForm } from './UserInfoForm';

export class CustomFieldTemplate extends React.Component {

  static propTypes = {
    propertyPath: PropTypes.string,
    title:        PropTypes.string,
    description:  PropTypes.string,
    field:        PropTypes.node,
    isHidden:     PropTypes.bool,
    formErrors:   PropTypes.object,
    isSubmit:     PropTypes.bool,
    onSubmit:     PropTypes.func,
    hiddenFields: PropTypes.any,  // eslint-disable-line react/forbid-prop-types
    required:     PropTypes.bool
  };

  render() {
    const { title, field, isHidden, propertyPath, formErrors, isSubmit, onSubmit, hiddenFields, required } = this.props;

    if (isHidden) {
      return field;
    }

    return (
      <UserInfoForm
        title={title}
        errors={formErrors}
        field={propertyPath}
        isSubmit={isSubmit}
        onSubmit={onSubmit}
        required={required}
      >
        {hiddenFields}
        {field}
      </UserInfoForm>
    );
  }
}
