import React, { PropTypes } from 'react';
import { injectIntl, FormattedMessage } from 'react-intl';
import { Segment } from 'DeskPRO/Component/Semantic/Segment';
import { Button } from 'DeskPRO/Component/Semantic/Button';
import { Form, TextArea } from 'DeskPRO/Component/Semantic/Form';

export class DeleteAccountFeedbackContainer extends React.Component {
  render() {
    return <DeleteAccountFeedback />;
  }
}

@injectIntl
export class DeleteAccountFeedback extends React.Component {
  static propTypes = {
    onSubmit: PropTypes.func
  };

  render() {
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
            placeholder={
              <FormattedMessage
                id="cloud.demo_expired.delete_account_feedback_placeholder"
                defaultMessage="Type feedback here..."
              />
            }
            rows={4}
          />
        </Form>
        <Button onClick={this.props.onSubmit}>
          <FormattedMessage
            id="cloud.demo_expired.delete_account_submit"
            defaultMessage="Submit feedback"
          />
        </Button>
      </Segment>
    );
  }
}
