import PropTypes from 'prop-types';
import React from 'react';
import { injectIntl, FormattedMessage } from 'react-intl';
import classNames from 'classnames';
import { Button } from 'DeskPRO/Component/Semantic/Button';
import { Form, TextArea } from 'DeskPRO/Component/Semantic/Form';

@injectIntl
class Question extends React.Component {
  static propTypes = {
    question:         PropTypes.string,
    questionOpened:   PropTypes.bool,
    submit:           PropTypes.bool,
    onChangeQuestion: PropTypes.func,
    onSubmitQuestion: PropTypes.func,
    onCloseQuestion:  PropTypes.func
  };

  renderForm() {
    return (
      <div>
        <h3>
          <FormattedMessage
            id="cloud.demo_expired.question_title"
            defaultMessage="How can we help you?"
          />
        </h3>
        <p>
          <FormattedMessage
            id="cloud.demo_expired.question_desc"
            defaultMessage="Please submit your question and our online agents get back to you ASAP."
          />
        </p>
        <Form>
          <TextArea
            id="question"
            name="question"
            value={this.props.question}
            onChange={this.props.onChangeQuestion}
            rows={4}
          />
        </Form>
        <Button onClick={this.props.onSubmitQuestion} className={classNames({ loading: this.props.submit })}>
          <FormattedMessage
            id="cloud.demo_expired.send_question"
            defaultMessage="Send"
          />
        </Button><br />
        <Button onClick={this.props.onCloseQuestion} className="basic">
          <FormattedMessage
            id="cloud.demo_expired.cancel"
            defaultMessage="Cancel"
          />
        </Button>
      </div>
    );
  }

  render() {
    if (this.props.questionOpened) {
      return (<PositionedQuestion>{this.renderForm()}</PositionedQuestion>);
    }
    return null;
  }
}
export default Question;

class PositionedQuestion extends React.Component {
  static propTypes = {
    children: PropTypes.node
  };

  render() {
    return (
      <div className="overlay">
        <div className="question-popin positioned-element">
          <div className="question">
            {this.props.children}
          </div>
        </div>
      </div>
    );
  }
}
