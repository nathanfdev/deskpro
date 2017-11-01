import PropTypes from 'prop-types';
import React from 'react';

export class Name extends React.Component {

  static propTypes = {
    value:    PropTypes.string,
    onChange: PropTypes.func.isRequired
  };

  onChange = (event) => {
    this.props.onChange(event.target.value);
  };

  render() {
    return (
      <div className="bucket-column">
        <input
          type="text"
          placeholder="Your name"
          value={this.props.value}
          onChange={this.onChange}
        />
      </div>
    );
  }
}
