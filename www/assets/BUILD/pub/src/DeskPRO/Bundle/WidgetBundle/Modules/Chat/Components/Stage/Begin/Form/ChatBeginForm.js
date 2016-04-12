import React, { PropTypes } from 'react';
import { FormItem } from './FormItem';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';

export class ChatBeginForm extends React.Component {

  static propTypes = {
    name:   PropTypes.string,
    email:  PropTypes.string,
    submit: PropTypes.bool,
    errors: PropTypes.object,

    onChangeName:  PropTypes.func,
    onChangeEmail: PropTypes.func,
    onSubmit:      PropTypes.func
  };

  render() {
    const { name, email, submit, errors } = this.props;
    const { onChangeName, onChangeEmail, onSubmit } = this.props;

    return (
      <div className="dpdesignportal-open-new-chat">
        <form className="dpdesignportal-form" onSubmit={onSubmit}>
          <FormItem label={portalPhrases.get('portal.chat.label-details')} field="name" errors={errors}>
            <input type="text"
                   placeholder={portalPhrases.get('portal.chat.details-placeholder')}
                   value={name}
                   onChange={onChangeName} />
          </FormItem>

          <FormItem label={portalPhrases.get('portal.chat.label-email')} field="email" errors={errors}>
            <input type="text"
                   placeholder="email@example.com"
                   value={email}
                   onChange={onChangeEmail} />
          </FormItem>

          <div className="button-label">
            {submit
              ? <div className="spinner"><i /></div>
              : <input type="submit"
                       value={portalPhrases.get('portal.chat.start')}
                       className="dpdesignportal-button dpdesignportal-button-wide" />
            }
          </div>
        </form>

      </div>
    );
  }
}
