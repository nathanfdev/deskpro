import React, { PropTypes } from 'react';
import { Segment } from 'DeskPRO/Component/Semantic/Segment';
import { Button } from 'DeskPRO/Component/Semantic/Button';
import { Form, TextArea } from 'DeskPRO/Component/Semantic/Form';

export class DeleteAccountFeedbackContainer extends React.Component {
  render() {
    return <DeleteAccountFeedback />;
  }
}
export class DeleteAccountFeedback extends React.Component {
  static propTypes = {
    onSubmit: PropTypes.func
  };

  render() {
    return (
      <Segment classes="delete-account-feedback">
        <h3>Sorry to see you go</h3>
        <p>
          Your data will be erased in the next 7 days.
        </p>
        <p>
          We're always looking to improve our product and would love to hear your feedback.
        </p>
        <Form>
          <TextArea placeholder="Type feedback here..." rows={4} />
        </Form>
        <Button onClick={this.props.onSubmit}>Submit feedback</Button>
      </Segment>
    );
  }
}
