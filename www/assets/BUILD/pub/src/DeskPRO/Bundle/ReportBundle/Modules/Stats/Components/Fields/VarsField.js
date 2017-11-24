import PropTypes from 'prop-types';
import React from 'react';
import { Field, Select, Input } from 'DeskPRO/Component/Semantic/ReactForm';
import { Fieldset } from '@deskpro/react-forms';
import DateField from './DateField';
import TypeField from './TypeField';
import TypeValueField from './TypeValueField';
import { varTypes } from '../helper';

class VarsField extends React.Component {

  static propTypes = {
    value:       PropTypes.array,
    groupParams: PropTypes.object,
    onChange:    PropTypes.func,
    fields:      PropTypes.object,
  };

  constructor(props) {
    super(props);
    this.onAddVarClick = this.onAddVarClick.bind(this);
  }

  onAddVarClick(event) {
    const { value, onChange } = this.props;

    event.preventDefault();
    onChange((value || []).concat([{ type: 'dates', name: 'new var' }]));
  }

  onDeleteVarClick(index) {
    const { value, onChange } = this.props;
    onChange(value.splice(index));
  }

  render() {
    const { groupParams } = this.props;

    return (<span>
      {this.props.fields && this.props.value.map((variable, index) =>
        (
          <Fieldset key={index} select={`${index}`}>
            <Field className="name" select="name" label="Name">
              <Input type="text" />
            </Field>
            <Field className="type" select="type">
              <Select choices={varTypes} clearable={false} />
            </Field>
            { variable.type === 'dates'
              ? <DateField  key={`date_${index}`} dates={groupParams.get('dates')} />
              : [
                <TypeField key={`field_type_${index}`} groupParams={groupParams} type={variable.type} />,
                <TypeValueField key={`field_value_type_${index}`} values={groupParams.getIn([variable.type, variable.field_type])} />
              ]
            }
            <i className="remove circle icon" onClick={() => this.onDeleteVarClick(index)} />
          </Fieldset>
        ))}
      <button onClick={this.onAddVarClick} className="ui button small">add var</button>
    </span>
    );
  }
}

export default VarsField;
