import React, { PropTypes } from 'react';
import Select from 'react-select';

export class Email extends React.Component {

  static propTypes = {
    emails: PropTypes.array.isRequired,
    primary: PropTypes.string.isRequired,
    onChangeEmails: PropTypes.func.isRequired,
    onChangePrimary: PropTypes.func.isRequired
  };

  renderSelectBox() {
    const { emails, onChangePrimary } = this.props;

    if (emails && emails.length > 0) {
      const options = emails.map(function(email) {
        return {
          value: email,
          label: email
        };
      });

      return (
        <Select
          name="form-field-name"
          value="one"
          options={options}
          onChange={onChangePrimary}
          searchable={false} />
      );
    }

    return null;
  }

  render() {
    const { emails, onChangeEmails } = this.props;

    return (
      <div className="bucket-column">
        <input type="text" placeholder="Your email" value={emails.join(', ')} onChange={onChangeEmails} />
        {this.renderSelectBox()}
      </div>
    );
  }
}
