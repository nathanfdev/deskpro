import PropTypes from 'prop-types';
import React from 'react';

export class Email extends React.Component {

  static propTypes = {
    emails:   PropTypes.array.isRequired,
    onChange: PropTypes.func.isRequired
  };

  onChange = (event) => {
    const value = (event.target.value || '').replace(/\s/g, '');
    const emails = value.split(',').map(email => email && email.trim() || '');

    this.props.onChange(emails);
  };

  render() {
    const { emails } = this.props;

    return (
      <div className="bucket-column">
        <input
          type="text"
          placeholder="Your email"
          value={emails.join(',')}
          onChange={this.onChange}
        />

        <span className="small field-note">Separate multiple email addresses with a comma.</span>
      </div>
    );
  }
}
