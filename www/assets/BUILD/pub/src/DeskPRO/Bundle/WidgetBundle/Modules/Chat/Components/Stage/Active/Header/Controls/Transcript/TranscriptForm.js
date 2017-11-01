import PropTypes from 'prop-types';
import React from 'react';
import { FormItem } from './FormItem';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';

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
        <h1>{portalPhrases.get('portal.chat.transcript_title')}</h1>
        <p className="grey">{portalPhrases.get('portal.chat.transcript_desc')}</p>

        <div className="popover-form">
          <form className="dpdesignportal-form" onSubmit={this.onSubmit}>
            <FormItem label={portalPhrases.get('portal.forms.label_full_name')} field="name" errors={this.state.errors}>
              <input type="text" value={this.state.name} onChange={this.onChangeName} />
            </FormItem>

            <FormItem label={portalPhrases.get('portal.chat.label-email')} field="email" errors={this.state.errors}>
              <input
                type="text"
                value={this.state.email}
                onChange={this.onChangeEmail}
                disabled={disabledEmail}
              />
            </FormItem>

            <div className="label button-label">
              {this.state.submit
                ? <div className="spinner"><i /></div>
                :
                <input
                  type="submit"
                  value={portalPhrases.get('portal.chat.transcript_action')}
                  className="dpdesignportal-button"
                  onClick={this.onSubmit}
                />
              }
            </div>

          </form>
        </div>
      </div>
    );
  }
}
