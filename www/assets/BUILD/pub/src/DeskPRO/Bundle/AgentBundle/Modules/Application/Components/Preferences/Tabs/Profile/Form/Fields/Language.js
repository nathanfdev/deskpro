import PropTypes from 'prop-types';
import React from 'react';
import Select from 'react-select';

export class Language extends React.Component {

  static propTypes = {
    languages: PropTypes.object.isRequired,
    value:     PropTypes.number,
    onChange:  PropTypes.func.isRequired
  };

  render() {
    const { value, languages, onChange } = this.props;
    const options = languages.map(language => ({
      value: language.get('id'),
      label: language.get('title')
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
