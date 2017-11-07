import PropTypes from 'prop-types';
import React from 'react';
import { injectIntl, FormattedMessage } from 'react-intl';
import { connect } from 'react-redux';
import classNames from 'classnames';
import { Segment } from 'DeskPRO/Component/Semantic/Segment';
import { Button } from 'DeskPRO/Component/Semantic/Button';
import { Message } from 'DeskPRO/Component/Semantic/Message';
import * as actions from '../Actions/extendActions';

@connect()
export class ConfirmResetContainer extends React.Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
  };

  static contextTypes = {
    router: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);

    this.state = {
      submit: false,
      errors: null
    };
  }

  onCancelButton = () => {
    this.context.router.push('/confirm-extend');
  };

  onReset = () => {
    const { dispatch } = this.props;
    this.setState({
      submit: true
    });

    const promise = dispatch(actions.resetTrial());

    promise.then(
      () => {
        window.location.href = window.DESKPRO_BASE_URL;
      },
      (response) => {
        if (this.mounted) {
          this.setState({
            submit: false,
            errors: response.getData().errors
          });
        }
      }
    );
  };

  render() {
    return (
      <ConfirmReset
        onCancelButton={this.onCancelButton}
        onReset={this.onReset}
        submit={this.state.submit}
      />
    );
  }
}

@injectIntl
export class ConfirmReset extends React.Component {
  static propTypes = {
    onCancelButton: PropTypes.func,
    onReset:        PropTypes.func,
    submit:         PropTypes.bool,
    errors:         PropTypes.object,
  };

  getError = () => {
    if (this.props.errors && this.props.errors.message) {
      return (
        <Message className="negative">
          {this.props.errors.message}
        </Message>
      );
    }
    return null;
  };

  render() {
    return (
      <Segment className="confirm-reset">
        <h3>
          <FormattedMessage
            id="cloud.demo_expired.confirm_reset_title"
            defaultMessage="Confirm trial reset"
          />
        </h3>
        <p>
          <FormattedMessage
            id="cloud.demo_expired.confirm_reset_desc"
            defaultMessage="This will erase all your data and you'll be starting a fresh trial."
          />
        </p>
        {this.getError()}
        <Button onClick={this.props.onReset} className={classNames({ loading: this.props.submit })}>
          <FormattedMessage
            id="cloud.demo_expired.reset_trial"
            defaultMessage="Reset trial"
          />
        </Button><br />
        <Button onClick={this.props.onCancelButton} className="basic">
          <FormattedMessage
            id="cloud.demo_expired.cancel"
            defaultMessage="Cancel"
          />
        </Button>
      </Segment>
    );
  }
}
