import React, { PropTypes } from 'react';
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
    hiddenFields: PropTypes.any
  };

  render() {
    const { title, field, isHidden, propertyPath, formErrors, isSubmit, onSubmit, hiddenFields } = this.props;

    if (isHidden) {
      return field;
    }

    return (
      <UserInfoForm title={title} errors={formErrors} field={propertyPath} isSubmit={isSubmit} onSubmit={onSubmit}>
        {hiddenFields}
        {field}
      </UserInfoForm>
    );
  }
}
