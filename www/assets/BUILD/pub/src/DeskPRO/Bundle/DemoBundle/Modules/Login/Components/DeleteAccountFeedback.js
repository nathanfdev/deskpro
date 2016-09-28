import React, { PropTypes } from 'react';
import { defineMessages, injectIntl, intlShape, FormattedMessage } from 'react-intl';
import { connect } from 'react-redux';
import classNames from 'classnames';
import { Segment } from 'DeskPRO/Component/Semantic/Segment';
import { Button } from 'DeskPRO/Component/Semantic/Button';
import { Form, TextArea } from 'DeskPRO/Component/Semantic/Form';
import * as actions from '../Actions/extendActions';

@connect()
export class DeleteAccountFeedbackContainer extends React.Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
  };

  constructor(props) {
    super(props);

    this.state = {
      feedback: '',
      submit:   false,
      errors:   null
    };
  }

  onChangeFeedback = (value) => {
    this.setState({
      feedback: value,
      errors:   null
    });
  };

  postDeleteFeedback = () => {
    const { dispatch } = this.props;
    this.setState({
      submit: true
    });

    const promise = dispatch(actions.deleteFeedback({
      feedback: this.state.feedback
    }));

    promise.then(
      () => {
        this.setState({
          submit: false,
        });
      },
      (response) => {
        this.setState({
          submit: false,
          errors: response.getData().errors
        });
      }
    );
  };

  render() {
    return (<DeleteAccountFeedback
      onSubmit={this.postDeleteFeedback}
      onChangeFeedback={this.onChangeFeedback}
      feedback={this.state.feedback}
      submit={this.state.submit}
    />);
  }
}

const messages = defineMessages({
  feedback_placeholder: {
    id:             'cloud.demo_expired.delete_account_feedback_placeholder',
    defaultMessage: 'Type feedback here...'
  }
});

@injectIntl
export class DeleteAccountFeedback extends React.Component {
  static propTypes = {
    intl:             intlShape.isRequired,
    feedback:         PropTypes.string,
    onChangeFeedback: PropTypes.func,
    onSubmit:         PropTypes.func,
    submit:           PropTypes.bool
  };

  render() {
    const { formatMessage } = this.props.intl;

    return (
      <Segment className="delete-account-feedback">
        <h3>
          <FormattedMessage
            id="cloud.demo_expired.delete_account_title"
            defaultMessage="Sorry to see you go"
          />
        </h3>
        <p>
          <FormattedMessage
            id="cloud.demo_expired.delete_account_desc"
            defaultMessage="Your data will be erased in the next 7 days."
          />
        </p>
        <p>
          <FormattedMessage
            id="cloud.demo_expired.delete_account_feedback"
            defaultMessage="We're always looking to improve our product and would love to hear your feedback."
          />
        </p>
        <Form>
          <TextArea
            placeholder={formatMessage(messages.feedback_placeholder)}
            onChange={this.props.onChangeFeedback}
            value={this.props.feedback}
            rows={4}
          />
        </Form>
        <Button onClick={this.props.onSubmit} className={classNames({ loading: this.props.submit })}>
          <FormattedMessage
            id="cloud.demo_expired.delete_account_submit"
            defaultMessage="Submit feedback"
          />
        </Button>
      </Segment>
    );
  }
}
