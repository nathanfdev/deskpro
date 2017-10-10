import React, { PropTypes } from 'react';
import { Field, Select } from 'DeskPRO/Component/Semantic/ReactForm';

class DateField extends React.Component {
  static propTypes = {
    dates: PropTypes.object,
  };

  render() {
    const choices = this.props.dates.map((date, index) => { const choice = { value: index, label: date.get(0) }; return choice; }).toList().toJS();

    return (
      <Field className="date-field" select="default">
        <Select choices={choices} />
      </Field>
    );
  }
}

export default DateField;
