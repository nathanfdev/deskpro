import PropTypes from 'prop-types';
import React from 'react';
import { Field, Select } from 'DeskPRO/Component/Semantic/ReactForm';

class TypeValueField extends React.Component {

  static defaultProps = {
    values: null
  };

  static propTypes = {
    values: PropTypes.object,
  };

  static renderEmpty() {
    return null;
  }

  renderSelect() {
    const choices = this.props.values
      .map((value, key) => { const choice = { label: value.get(0), value: key }; return choice; })
      .toList()
      .toJS();

    return (
      <Field className="field-type-value" select="default">
        <Select choices={choices} />
      </Field>
    );
  }

  render() {
    return this.props.values ? this.renderSelect() : TypeValueField.renderEmpty();
  }
}

export default TypeValueField;
