import PropTypes from 'prop-types';
import React from 'react';
import { BannedMessage, FormErrorMessage } from './FormMessages';

export class ChatBeginSimple extends React.Component {

  static propTypes = {
    banned:   PropTypes.bool,
    errors:   PropTypes.object,
    onSubmit: PropTypes.func
  };

  componentDidMount() {
    this.props.onSubmit();
  }

  renderContent() {
    const { banned, errors } = this.props;

    if (banned) {
      return <BannedMessage />;
    }
    if (errors && errors.errors) {
      return <FormErrorMessage errors={errors.errors} />;
    }

    return <div className="spinner"><i /></div>;
  }

  render() {
    return (
      <div className="dpdesignportal-collect-user-info-waiting">
        {this.renderContent()}
      </div>
    );
  }
}
