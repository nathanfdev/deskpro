import PropTypes from 'prop-types';
import React from 'react';
import { Field } from 'react-forms';
import { AbstractCustomField } from './AbstractCustomField';
import PortalSimpleSelectBoxWrapper from './PortalSimpleSelectBoxWrapper';

export class CustomFieldSingleChoice extends AbstractCustomField {

  render() {
    const { name, config, widgetOptions } = this.props;

    return (
      <Field select={name}>
        <DeepSelectBox
          key={config.get('id')}
          choices={config.get('choices')}
          widgetOptions={widgetOptions}
        />
      </Field>
    );
  }
}

class DeepSelectBox extends React.Component {

  static propTypes = {
    value:         PropTypes.number,
    choices:       PropTypes.object,
    onChange:      PropTypes.func,
    widgetOptions: PropTypes.object
  };

  renderDeep(choices, deep = 1) {
    const { value, onChange, widgetOptions } = this.props;

    const hasValue = (choice) => {
      if (choice.get('id') === value) {
        return true;
      }
      if (choice.get('children')) {
        let hasChild = false;
        choice.get('children').forEach((child) => {
          if (hasValue(child)) {
            hasChild = true;
          }
        });

        if (hasChild) {
          return true;
        }
      }

      return false;
    };

    let subChoice = null;
    if (value && choices) {
      choices.forEach((choice) => {
        if (hasValue(choice)) {
          subChoice = choice;
        }
      });
    }

    return (
      <div>
        <PortalSimpleSelectBoxWrapper
          value={subChoice && subChoice.get('id')}
          level={deep}
          onChange={onChange}
          choices={choices}
          widgetOptions={widgetOptions}
        />
        {subChoice && subChoice.get('children') && this.renderDeep(subChoice.get('children'), deep + 1)}
      </div>
    );
  }

  render() {
    return this.renderDeep(this.props.choices);
  }
}
