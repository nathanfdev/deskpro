import React, { PropTypes } from 'react';
import { Fieldset, createValue } from 'react-forms';
import { Form, Field } from 'DeskPRO/Component/Semantic/ReactForm';
import { Button } from 'DeskPRO/Component/Semantic/Button';
import $ from 'jquery';
import Immutable from 'immutable';
import NumberSelect from '../NumberSelect';
import DialGrid from '../../Common/DialGrid';

class Dialpad extends React.Component {

  static propTypes = {
    callFromNumbers: PropTypes.object
  };

  constructor(props) {
    super(props);
    this.state = {
      formData: createValue({
        value: {
          call_from: '',
          call_to:   ''
        },
        onChange: this.onChange
      })
    };
  }

  onChange = (formData) => {
    this.setState({ formData });
  };

  onSubmit = (event) => {
    event.preventDefault();
  };

  onClickNumber = (number) => {
    const $input = $(this.callToInput);
    const { formData } = this.state;
    this.setState({
      formData: createValue({
        value:    { ...formData.value, call_to: `${formData.value.call_to}${number}` },
        onChange: this.onChange
      })
    }, () => $input.focus());
  };

  render() {
    const { callFromNumbers = Immutable.fromJS({}) } = this.props;
    const { formData } = this.state;

    return (
      <div className="dialpad">
        <Form formValue={formData} onSubmit={this.onSubmit}>
          <Fieldset>
            <Field select="call_from" label="Call from">
              <NumberSelect numbers={callFromNumbers} />
            </Field>

            <div className="ui icon input">
              <i className="search icon" />
              <Field select="call_to">
                <input
                  type="text"
                  className="voice-dialpad-input"
                  ref={(c) => { this.callToInput = c; }}
                />
              </Field>
            </div>

            <DialGrid onClick={this.onClickNumber} />

            <Button className="green call-button">
              <i className="icon call" />
              Call
            </Button>
          </Fieldset>
        </Form>
      </div>
    );
  }
}

export default Dialpad;
