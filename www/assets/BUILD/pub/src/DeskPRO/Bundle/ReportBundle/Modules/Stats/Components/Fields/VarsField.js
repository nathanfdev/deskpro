import React, { PropTypes } from 'react';
import { Field, Select, Input } from 'DeskPRO/Component/Semantic/ReactForm';
import { Fieldset } from 'react-forms';
import DateField from './DateField';
import TypeField from './TypeField';
import TypeValueField from './TypeValueField';


class VarsField extends React.Component {

  static propTypes = {
    value:       PropTypes.array,
    groupParams: PropTypes.object
  };

  static types = [
    {
      label: 'Date',
      value: 'dates',
    },
    {
      label: 'Status',
      value: 'statuses',
    },
    {
      label: 'Field',
      value: 'fields',
    },
    {
      label: 'Order',
      value: 'orders',
    },
  ];

  render() {
    const { groupParams } = this.props;

    return (<span>
      {this.props.value.map((variable, index) =>
        (
          <Fieldset key={index} select={`${index}`}>
            <Field className="name" select="name" label="Name">
              <Input type="text" />
            </Field>
            <Field className="type" select="type">
              <Select choices={VarsField.types} />
            </Field>
            { variable.type === 'dates'
              ? <DateField  key={`date_${index}`} dates={groupParams.get('dates')} />
              : [
                <TypeField key={`field_type_${index}`} groupParams={groupParams} type={variable.type} />,
                <TypeValueField key={`field_value_type_${index}`} values={groupParams.getIn([variable.type, variable.field_type])} />
              ]
            }
          </Fieldset>
        ))}
    </span>
    );
  }
}

export default VarsField;
