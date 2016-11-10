import React, { PropTypes } from 'react';
import { Fieldset, createValue } from 'react-forms';
import { Form, Field } from 'DeskPRO/Component/Semantic/ReactForm';
import Accordion from 'DeskPRO/Component/Semantic/Accordion/Accordion';
import QueuesToggleContainer from './QueuesToggleContainer';
import Volume from './Volume';
import CallForward from './CallForward';

class Settings extends React.Component {

  static propTypes = {
    onChange: PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      formData: createValue({
        value: {
          queues:       [],
          volume:       10,
          call_forward: {
            enable_forward: false,
            number:         ''
          }
        },
        onChange: this.onChange
      })
    };
  }

  onChange = (formData) => {
    this.setState({ formData });
    this.props.onChange(formData.value);
  };

  render() {
    const { formData } = this.state;
    const structureNodes = {
      panels: [
        {
          title: (
            <span>
              <i className="fa fa-tasks" />
              Queues
            </span>
          ),
          content: (
            <div>
              <Field select="queues">
                <QueuesToggleContainer />
              </Field>
            </div>
          )
        },
        {
          title: (
            <span>
              <i className="fa fa-volume-up" />
              Ringing volume
            </span>
          ),
          content: (
            <div>
              <Field select="volume">
                <Volume />
              </Field>
            </div>
          )
        },
        {
          title: (
            <span>
              <i className="fa fa-mail-forward" />
              Call forwarding
            </span>
          ),
          content: (
            <div>
              <Field select="call_forward">
                <CallForward />
              </Field>
            </div>
          )
        }
      ]
    };

    return (
      <Form formValue={formData}>
        <Fieldset>
          <Accordion {...structureNodes} />
        </Fieldset>
      </Form>
    );
  }
}

export default Settings;
