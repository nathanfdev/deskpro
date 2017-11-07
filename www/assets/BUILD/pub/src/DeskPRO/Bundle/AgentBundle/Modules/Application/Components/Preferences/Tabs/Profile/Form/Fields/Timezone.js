import PropTypes from 'prop-types';
import React from 'react';
import Select from 'react-select';

export class Timezone extends React.Component {

  static propTypes = {
    timezones: PropTypes.object.isRequired,
    value:     PropTypes.string,
    onChange:  PropTypes.func.isRequired
  };

  render() {
    const { value, timezones, onChange } = this.props;
    const options = timezones.map(timezone => ({
      value: timezone.get('title'),
      label: timezone.get('title')
    })).toArray();

    return (
      <div className="bucket-column">
        <Select
          name="form-field-name"
          value={value}
          options={options}
          clearable={false}
          onChange={onChange}
        />
      </div>
    );
  }
}
