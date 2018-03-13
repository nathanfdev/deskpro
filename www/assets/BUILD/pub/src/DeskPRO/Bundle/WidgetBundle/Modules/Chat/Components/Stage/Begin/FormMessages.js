import React from 'react';
import PropTypes from 'prop-types';
import { portalPhrases } from 'DeskPRO/Bundle/PortalBundle/PortalPhrases';

export class BannedMessage extends React.Component {

  render() {
    return (
      <div className="inline-form-alert">
        <div className="form-alert-icon">
          <i className="fa fa-lock" />
        </div>
        <div className="content-wrapper">
          <h1>{portalPhrases.get('portal.chat.user_is_blocked')}</h1>
        </div>
      </div>
    );
  }
}

export class FormErrorMessage extends React.Component {

  static propTypes = {
    errors: PropTypes.array
  };

  render() {
    const { errors } = this.props;
    if (!errors[0]) {
      return null;
    }

    return (
      <div className="inline-form-alert">
        <div className="form-alert-icon">
          <i className="fa fa-lock" />
        </div>
        <div className="content-wrapper">
          <h1>{portalPhrases.get(`portal.chat.${errors[0].code}`)}</h1>
        </div>
      </div>
    );
  }
}
