import React from 'react';
import { Fieldset, createValue } from '@deskpro/react-forms';
import { Form, Field, Toggle, BlurInput } from 'DeskPRO/Component/Semantic/ReactForm';

class CallForward extends React.Component {

  constructor(props) {
    super(props);
    this.state = {
      formData: createValue({
        value: {
          enable_forward: false,
          number:         ''
        },
        onChange: this.onChange
      })
    };
  }

  onChange = (formData) => {
    this.setState({ formData });
  };

  render() {
    const { formData } = this.state;

    return (
      <div className="call-forward">
        <Form formValue={formData}>
          <Fieldset>
            <Field select="enable_forward">
              <Toggle className="small">
                Enable call forwarding
              </Toggle>
            </Field>
            <Field select="number" label="Forwarding number">
              <BlurInput type="text" />
            </Field>
          </Fieldset>
        </Form>
      </div>
    );
  }
}

export default CallForward;
