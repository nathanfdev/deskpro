import React, { PropTypes } from 'react';
import jQuery from 'jquery';

export class Email extends React.Component {

  static propTypes = {
    emails: PropTypes.array.isRequired,
    primary: PropTypes.string,
    onChangeEmails: PropTypes.func.isRequired,
    onChangePrimary: PropTypes.func.isRequired
  };

  onChangeEmails = (event) => {
    const value = (event.target.value || '').replace(/\s/g, '');
    const emails = value.split(',').map(email => email && email.trim() || '');

    this.props.onChangeEmails(emails);
  };

  onChangePrimary = (event) => {
    const value = event.target.value;
    this.props.onChangePrimary(value);
  };

  renderSelectBox() {
    const { emails, primary } = this.props;

    if (emails && emails.length > 1 && emails[1]) {
      const options = jQuery.unique(emails).map(email => ({
        value: email,
        label: email
      }));

      return (
        <select value={primary} onChange={this.onChangePrimary}>
          {options.map(option => <option key={option.value} value={option.value}>{option.label}</option>)}
        </select>
      );
    }

    return null;
  }

  render() {
    const { emails } = this.props;

    return (
      <div className="bucket-column">
        <input type="text" placeholder="Your email" value={emails.join(',')} onChange={this.onChangeEmails} />
        {this.renderSelectBox()}
      </div>
    );
  }
}
