import React, { PropTypes } from 'react';
import { Fieldset, createValue } from 'react-forms';
import { Form, Field, BlurInput } from 'DeskPRO/Component/Semantic/ReactForm';
import QueuesSelectContainer from '../../Common/NumberTarget/QueuesSelectContainer';

class NumberOptionsForm extends React.Component {

  static propTypes = {
    number:     PropTypes.object.isRequired,
    onSubmit:   PropTypes.func,
    onAddQueue: PropTypes.func
  };

  constructor(props) {
    super(props);
    const number = props.number;
    const targetType = number.getIn(['target', 'type']);

    this.state = {
      formData: createValue({
        value: {
          nickname:     number.get('nickname') || '',
          target_queue: targetType === 'queue' ? number.getIn(['target', 'queue']) : null
        },
        errorList: {},
        onChange:  this.onChange
      })
    };
  }

  onChange = (formData) => {
    this.setState({ formData });
    const value = formData.value;

    let submitData = { nickname: value.nickname };
    if (value.target_queue) {
      submitData = {
        ...submitData,
        target: {
          queue: value.target_queue,
          type:  'queue'
        }
      };
    }

    this.props.onSubmit(submitData);
  };

  render() {
    const { onAddQueue } = this.props;

    return (
      <Form onSubmit={(event) => { event.preventDefault(); }} formValue={this.state.formData}>
        <Fieldset>
          <Field
            select="nickname"
            label="Nickname"
            help="These can be used to more quickly find and identify numbers e.g. for assigning as part of an IVR menu."
          >
            <BlurInput placeholder="e.g. 'Primary Sales number'" />
          </Field>
          <Field select="target_queue" label="Queue">
            <QueuesSelectContainer />
          </Field>
          <button className="ui basic button" onClick={onAddQueue}>
            Add another queue
          </button>
        </Fieldset>
      </Form>
    );
  }
}

export default NumberOptionsForm;
