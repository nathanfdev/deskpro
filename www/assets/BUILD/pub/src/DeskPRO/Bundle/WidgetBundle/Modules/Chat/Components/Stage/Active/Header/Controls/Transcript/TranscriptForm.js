import React, { PropTypes } from 'react';
import { FormItem } from './FormItem';

export class TranscriptForm extends React.Component {

  static propTypes = {
    disabledEmail: PropTypes.bool,
    name:          PropTypes.string,
    email:         PropTypes.string,
    onSubmit:      PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);
    this.state = {
      name:   props.name || '',
      email:  props.email || '',
      submit: false,
      errors: null
    };
  }

  componentDidMount() {
    this.mounted = true;
  }

  componentWillUnmount() {
    this.mounted = false;
  }

  onChangeName = event => {
    this.setState({
      name:   event.target.value,
      errors: null
    });
  };

  onChangeEmail = event => {
    this.setState({
      email:  event.target.value,
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
          if (this.mounted) {
            this.setState({
              submit: false
            });
          }
        },
        result => {
          if (this.mounted) {
            this.setState({
              submit: false,
              errors: result.getData()
            });
          }
        }
      );
    }
  };

  render() {
    const { disabledEmail } = this.props;

    return (
      <div>
        <h1>Need a transcript of this chat?</h1>
        <p className="grey">Enter your name &amp; email below and we'll email it to you.</p>

        <div className="popover-form">
          <form className="dpdesignportal-form" onSubmit={this.onSubmit}>
            <FormItem label="Your name" field="name" errors={this.state.errors}>
              <input type="text" value={this.state.name} onChange={this.onChangeName} />
            </FormItem>

            <FormItem label="Your email" field="email" errors={this.state.errors}>
              <input type="text"
                     value={this.state.email}
                     onChange={this.onChangeEmail}
                     disabled={disabledEmail} />
            </FormItem>

            <div className="label button-label">
              {this.state.submit
                ? <div className="spinner"><i /></div>
                : <input type="submit"
                         value="Send me a transcript"
                         className="dpdesignportal-button"
                         onClick={this.onSubmit} />
              }
            </div>

          </form>
        </div>
      </div>
    );
  }
}
