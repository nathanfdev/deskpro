import React, { PropTypes } from 'react';
import { FormItem } from './FormItem';

export class CustomFieldTemplate extends React.Component {

  static propTypes = {
    propertyPath: PropTypes.string,
    title:        PropTypes.string,
    description:  PropTypes.string,
    field:        PropTypes.node,
    isHidden:     PropTypes.bool,
    formErrors:   PropTypes.object,
    required:     PropTypes.bool
  };

  render() {
    const { title, description, field, isHidden, propertyPath, formErrors, required } = this.props;

    if (isHidden) {
      return field;
    }

    return (
      <FormItem
        label={title}
        errors={formErrors}
        field={propertyPath}
        required={required}
      >
        {description && <p className="field-description">{description}</p>}
        {field}
      </FormItem>
    );
  }
}
