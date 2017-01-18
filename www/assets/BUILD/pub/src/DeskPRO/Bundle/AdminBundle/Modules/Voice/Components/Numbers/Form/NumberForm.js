import React, { PropTypes } from 'react';
import { Fieldset, Input, createValue } from 'react-forms';
import classNames from 'classnames';
import { Form, Field, Checkbox } from 'DeskPRO/Component/Semantic/ReactForm';
import SectionHeader from '../../../../Common/Components/SectionHeader';
import BackButton from '../../../../Common/Components/BackButton';
import QueuesSelectContainer from '../../Common/NumberTarget/QueuesSelectContainer';

class NumberForm extends React.Component {

  static propTypes = {
    number:       PropTypes.object,
    onReturnBack: PropTypes.func.isRequired,
    onSubmit:     PropTypes.func,
    saving:       PropTypes.bool
  };

  constructor(props) {
    super(props);
    this.state = this.getDefaultState();
  }

  onChange = (formData) => {
    this.setState({ formData });
  };

  onSubmit = (event) => {
    event.preventDefault();

    const { onSubmit } = this.props;
    const { formData } = this.state;
    const value = formData.value;

    let submitData = {
      nickname:               value.nickname,
      outbound_calls_enabled: value.outbound_calls_enabled
    };

    if (value.target_queue) {
      submitData = {
        ...submitData,
        target: {
          queue: value.target_queue,
          type:  'queue'
        }
      };
    }

    onSubmit(submitData);
  };

  onCancel = (event) => {
    event.preventDefault();
    this.setState(this.getDefaultState());
  };

  getDefaultState() {
    const { number } = this.props;
    const targetType = number.getIn(['target', 'type']);

    return {
      formData: createValue({
        value: {
          number:                 number.get('number'),
          nickname:               number.get('nickname') || '',
          target_queue:           targetType === 'queue' ? number.getIn(['target', 'queue']) : null,
          outbound_calls_enabled: number.get('outbound_calls_enabled')
        },
        errorList: {},
        onChange:  this.onChange
      })
    };
  }

  render() {
    const { onReturnBack, saving } = this.props;
    const { formData } = this.state;

    return (
      <div className="page">
        <BackButton onClick={onReturnBack} />
        <SectionHeader title="Update number" dividing />

        <div className="twilio-queue-form">
          <Form onSubmit={this.onSubmit} formValue={formData}>
            <Fieldset>
              <Field
                select="number"
                label="Number"
              >
                <Input disabled="disabled" />
              </Field>
              <Field
                select="nickname"
                label="Nickname"
                help="These can be used to more quickly find and identify numbers e.g. for assigning as part of an IVR menu."
              >
                <Input placeholder="My Nickname" />
              </Field>
              <Field select="target_queue" label="Queue">
                <QueuesSelectContainer />
              </Field>
              <Field select="outbound_calls_enabled">
                <Checkbox label="Allow outbound calls from this number" />
              </Field>

              <button className={classNames('ui button', { loading: saving })}>
                Update
              </button>
              <button
                className={classNames('ui basic button cancel-button', { disabled: saving })}
                onClick={this.onCancel}
              >
                Cancel
              </button>
            </Fieldset>
          </Form>
        </div>
      </div>
    );
  }
}

export default NumberForm;
