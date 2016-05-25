import React, { PropTypes } from 'react';
import { FormItem } from './FormItem';

export class CustomFieldTemplate extends React.Component {

  static propTypes = {
    propertyPath: PropTypes.string,
    title:        PropTypes.string,
    description:  PropTypes.string,
    field:        PropTypes.node,
    isHidden:     PropTypes.bool,
    formErrors:   PropTypes.object
  };

  render() {
    const { title, description, field, isHidden, propertyPath, formErrors } = this.props;

    if (isHidden) {
      return field;
    }

    return (
      <FormItem label={title} errors={formErrors} field={propertyPath}>
        {description && <p className="field-description">{description}</p>}
        {field}
      </FormItem>
    );
  }
}
