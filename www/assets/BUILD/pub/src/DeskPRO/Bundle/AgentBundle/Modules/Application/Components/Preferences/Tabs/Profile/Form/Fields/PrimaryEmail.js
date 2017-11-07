import PropTypes from 'prop-types';
import React from 'react';
import Select from 'react-select';
import jQuery from 'jquery';

export class PrimaryEmail extends React.Component {

  static propTypes = {
    emails:   PropTypes.array.isRequired,
    value:    PropTypes.string,
    onChange: PropTypes.func.isRequired
  };

  render() {
    const { emails, value, onChange } = this.props;

    const options = jQuery.unique(emails).map(email => ({
      value: email,
      label: email
    }));

    return (
      <div className="bucket-column">
        <Select
          name="form-field-name"
          value={value}
          options={options}
          searchable={false}
          clearable={false}
          onChange={onChange}
        />
      </div>
    );
  }
}
