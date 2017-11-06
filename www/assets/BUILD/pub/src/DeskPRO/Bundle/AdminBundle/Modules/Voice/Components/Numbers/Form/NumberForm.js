import PropTypes from 'prop-types';
import React from 'react';
import { Fieldset } from '@deskpro/react-forms';
import classNames from 'classnames';
import { Input, Form, Field, Checkbox } from 'DeskPRO/Component/Semantic/ReactForm';
import BaseForm from 'DeskPRO/Component/Form/BaseForm';
import SectionHeader from '../../../../Common/Components/SectionHeader';
import BackButton from '../../../../Common/Components/BackButton';
import NumberTargetSelect from '../../Common/NumberTarget/NumberTargetSelect';

class NumberForm extends BaseForm {

  static propTypes = {
    number:       PropTypes.object,
    onReturnBack: PropTypes.func.isRequired,
    onSubmit:     PropTypes.func,
    onDelete:     PropTypes.func
  };

  onCancel = (event) => {
    event.preventDefault();
    this.props.onReturnBack();
  };

  onDelete = (event) => {
    event.preventDefault();
    this.props.onDelete();
  };

  getDefaultState() {
    const { number } = this.props;

    return {
      number:                 number.get('number'),
      nickname:               number.get('nickname') || '',
      target:                 number.get('target') && number.get('target').toJS(),
      outbound_calls_enabled: number.get('outbound_calls_enabled')
    };
  }

  transformSubmitData(submitData) { // eslint-disable-line
    delete submitData.number;
    return submitData;
  }

  render() {
    const { onReturnBack } = this.props;
    const { formData, saving } = this.state;

    return (
      <div className="page">
        <BackButton onClick={onReturnBack} />
        <SectionHeader title="Update number" dividing />

        <div className="twilio-number-form">
          <Form onSubmit={this.onSubmit} formValue={formData}>
            <Fieldset>
              <Field select="number" label="Number">
                <Input disabled="disabled" />
              </Field>
              <Field
                select="nickname"
                label="Nickname"
                help="These can be used to more quickly find and identify numbers e.g. for assigning as part of an IVR menu."
              >
                <Input placeholder="My Nickname" />
              </Field>
              <Field select="target" label="Target" className="number-target">
                <NumberTargetSelect />
              </Field>
              <Field select="outbound_calls_enabled" className="allow-outbound-calls">
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
              <span className="voice-delete-button" onClick={this.onDelete}>
                Delete this number
              </span>
            </Fieldset>
          </Form>
        </div>
      </div>
    );
  }
}

export default NumberForm;
