import React, { PropTypes } from 'react';
import { CustomFieldText } from './CustomFieldText';
import { CustomFieldTextarea } from './CustomFieldTextarea';
import { CustomFieldDateTime } from './CustomFieldDateTime';
import { CustomFieldDisplay } from './CustomFieldDisplay';
import { CustomFieldSingleChoice } from './CustomFieldSingleChoice';
import { CustomFieldMultiChoice } from './CustomFieldMultiChoice';
import { CustomFieldRadio } from './CustomFieldRadio';
import { CustomFieldCheckbox } from './CustomFieldCheckbox';
import { CustomFieldHidden } from './CustomFieldHidden';
import { CustomFieldToggle } from './CustomFieldToggle';

export class CustomField extends React.Component {

  static propTypes = {
    config:        PropTypes.object,
    propertyPath:  PropTypes.string,
    formErrors:    PropTypes.object,
    children:      PropTypes.node,
    widgetOptions: PropTypes.object
  };

  static getFieldPropertyPath(props) {
    const { propertyPath = 'fields' } = props;
    return `${propertyPath}.${props.config.get('id')}`;
  }

  renderCustomField() {
    const { config } = this.props;
    const widgetType = config.get('widget_type');
    const widgetProps = {
      ...this.props,
      name: CustomField.getFieldPropertyPath(this.props)
    };

    switch (widgetType) {
      case 'text':
        return <CustomFieldText {...widgetProps} />;
      case 'textarea':
        return <CustomFieldTextarea {...widgetProps} />;
      case 'date':
        return <CustomFieldDateTime {...widgetProps} />;
      case 'datetime':
        return <CustomFieldDateTime {...widgetProps} timePicker />;
      case 'display':
        return <CustomFieldDisplay {...widgetProps} />;
      case 'choice':
        return <CustomFieldSingleChoice {...widgetProps} />;
      case 'multichoice':
        return <CustomFieldMultiChoice {...widgetProps} />;
      case 'radio':
        return <CustomFieldRadio {...widgetProps} />;
      case 'checkbox':
        return <CustomFieldCheckbox {...widgetProps} />;
      case 'hidden':
        return <CustomFieldHidden {...widgetProps} />;
      case 'toggle':
        return <CustomFieldToggle {...widgetProps} />;
      default:
        return null;
    }
  }

  render() {
    const { config, children, formErrors } = this.props;
    const childProps = children.props;
    const widgetType = config.get('widget_type');

    return React.cloneElement(children, {
      ...childProps,

      propertyPath: CustomField.getFieldPropertyPath(this.props),
      title:        config.get('title'),
      description:  config.get('description'),
      field:        this.renderCustomField(),
      isHidden:     widgetType === 'hidden',
      formErrors
    });
  }
}
