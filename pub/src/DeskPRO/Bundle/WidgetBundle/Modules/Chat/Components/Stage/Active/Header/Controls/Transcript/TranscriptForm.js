import React, { PropTypes } from 'react';
import { FormItem } from './FormItem';
import { hasErrors } from 'DeskPRO/Component/Form/FormErrors';

export class TranscriptForm extends React.Component {

  static propTypes = {
    name: PropTypes.string,
    email: PropTypes.string,
    onSubmit: PropTypes.func.isRequired,
    onClose: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);
    this.state = {
      name: props.name || '',
      email: props.email || '',
      submit: false,
      errors: null
    };
  }

  onChangeName = event => {
    this.setState({
      name: event.target.value,
      errors: null
    });
  };

  onChangeEmail = event => {
    this.setState({
      email: event.target.value,
      errors: null
    });
  };

  onSubmit = event => {
    event.preventDefault();

    const promise = this.props.onSubmit(this.state);
    if (promise) {
      this.setState({
        submit: true
      });

      promise.then(
        () => {
          this.setState({
            submit: false
          });
        },
        result => {
          this.setState({
            submit: false,
            errors: result.getData().errors
          });
        }
      );
    }
  };

  render() {
    return (
      <div className="dpdesignportal-popover dpdesignportal-popover-request-transcript">
        <div className="dpdesignportal-popover-close" onClick={this.props.onClose}>
          <i className="fa fa-times"></i>
        </div>

        <h1>Need a transcript of this chat?</h1>
        <p className="grey">Enter your name &amp; email below and we'll email it to you.</p>

        <div className="popover-form">
          <form className="dpdesignportal-form" onSubmit={this.onSubmit}>
            <FormItem label="Your name" error={hasErrors(this.state.errors, 'name')}>
              <input type="text" value={this.state.name} onChange={this.onChangeName} />
            </FormItem>

            <FormItem label="Your email" error={hasErrors(this.state.errors, 'email')}>
              <input type="text" value={this.state.email} onChange={this.onChangeEmail} />
            </FormItem>

            <div className="label button-label">
              {this.state.submit
                ? 'Saving'
                : <input type="submit" value="Send me a transcript" className="dpdesignportal-button" onClick={this.onSubmit} />
              }
            </div>

          </form>
        </div>
      </div>
    );
  }
}
