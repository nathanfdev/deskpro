import React, { PropTypes } from 'react';
import { FormItem } from './FormItem';
import { hasErrors } from 'DeskPRO/Component/Form/FormErrors';

export class ChatBeginForm extends React.Component {

  static propTypes = {
    name: PropTypes.string,
    email: PropTypes.string,
    submit: PropTypes.bool,
    errors: PropTypes.object,

    onChangeName: PropTypes.func,
    onChangeEmail: PropTypes.func,
    onSubmit: PropTypes.func
  };

  render() {
    const { name, email, submit, errors } = this.props;
    const { onChangeName, onChangeEmail, onSubmit } = this.props;

    return (
      <div className="dpdesignportal-open-new-ticket">
        <form className="dpdesignportal-form" onSubmit={onSubmit}>
          <FormItem label="Your Details"
                    error={hasErrors(errors, 'name')}>

            <input type="text"
                   placeholder="First name, Last name"
                   value={name}
                   onChange={onChangeName} />
          </FormItem>

          <FormItem label="Your Email"
                    error={hasErrors(errors, 'email')}>

            <input type="text"
                   placeholder="email@example.com"
                   value={email}
                   onChange={onChangeEmail} />
          </FormItem>

          <div className="button-label">
            {submit
              ? <div className="spinner"><i/></div>
              : <input type="submit"
                       value="Start a new chat"
                       className="dpdesignportal-button dpdesignportal-button-wide" />
            }
          </div>
        </form>

      </div>
    );
  }
}
