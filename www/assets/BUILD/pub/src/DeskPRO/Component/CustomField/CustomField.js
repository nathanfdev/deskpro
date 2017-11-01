import PropTypes from 'prop-types';
import React from 'react';
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
    config:     PropTypes.object,
    language:   PropTypes.number,
    formErrors: PropTypes.object,
    children:   PropTypes.node
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
    const { config, language, children, formErrors } = this.props;
    const childProps = children.props;
    const widgetType = config.get('widget_type');

    let title = config.get('title');
    let description = config.get('description');

    const translation = config.getIn(['translations', `${language}`]);
    if (translation) {
      title = translation.get('title');
      description = translation.get('description');
    }

    return React.cloneElement(children, {
      ...childProps,

      title,
      description,
      formErrors,
      propertyPath: CustomField.getFieldPropertyPath(this.props),
      field:        this.renderCustomField(),
      isHidden:     widgetType === 'hidden',
      required:     config.getIn(['options', 'required']) || config.getIn(['options', 'validation_type']) === 'required'
    });
  }
}
