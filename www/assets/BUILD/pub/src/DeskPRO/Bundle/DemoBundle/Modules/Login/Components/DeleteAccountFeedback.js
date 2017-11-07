import PropTypes from 'prop-types';
import React from 'react';
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
      done:     false,
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
          done: true,
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
    if (this.state.done) {
      return (<DeleteAccountDone />);
    }

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
class DeleteAccountFeedback extends React.Component {
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
        <Button onClick={this.props.onSubmit} className={classNames('negative', { loading: this.props.submit })}>
          <FormattedMessage
            id="cloud.demo_expired.delete_account_feedback_submit"
            defaultMessage="Submit Feedback &amp; Delete Account"
          />
        </Button>
      </Segment>
    );
  }
}

class DeleteAccountDone extends React.Component {
  render() {
    return (
      <Segment className="delete-account-feedback">
        <h3>
          <FormattedMessage
            id="cloud.demo_expired.delete_done_title"
            defaultMessage="Your account has been deleted"
          />
        </h3>
        <p>
          <FormattedMessage
            id="cloud.demo_expired.delete_done_info"
            defaultMessage="Your account has been deleted. All data will be purged from our systems within 7 days."
          />
        </p>
      </Segment>
    );
  }
}
