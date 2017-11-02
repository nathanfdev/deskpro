import React, { PropTypes } from 'react';
import { Field, Select } from 'DeskPRO/Component/Semantic/ReactForm';

class TypeField extends React.Component {
  static propTypes = {
    groupParams: PropTypes.object,
    type:        PropTypes.string
  };

  static renderEmpty() {
    return null;
  }

  renderSelect() {
    const choices = this.props.groupParams.get(this.props.type)
      .keySeq()
      .map((value) => { const choice = { label: value, value }; return choice; })
      .toList()
      .toJS();

    return (
      <Field className="field-type" select="field_type">
        <Select choices={choices}  clearable={false} />
      </Field>
    );
  }

  render() {
    return this.props.type ? this.renderSelect() : TypeField.renderEmpty();
  }
}

export default TypeField;
