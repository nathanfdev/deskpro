import PropTypes from 'prop-types';
import React, { Component } from 'react';
import { Fieldset } from '@deskpro/react-forms';
import classNames from 'classnames';
import { Input, Form, Field, Checkbox, Radio, CountryCodeSelect } from 'DeskPRO/Component/Semantic/ReactForm';
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
      number:                           number.get('number'),
      nickname:                         number.get('nickname') || '',
      target:                           number.get('target') && number.get('target').toJS(),
      outbound_calls_enabled:           number.get('outbound_calls_enabled'),
      outbound_calls_default:           number.get('outbound_calls_default'),
      outbound_calls_default_global:    number.get('outbound_calls_default_global') ? 1 : 0,
      outbound_calls_default_countries: number.get('outbound_calls_default_countries') ? number.get('outbound_calls_default_countries').toJS() : [],
    };
  }

  transformSubmitData(submitData) { // eslint-disable-line
    delete submitData.number;
    return submitData;
  }

  render() {
    const { number, onReturnBack } = this.props;
    const { formData, saving } = this.state;

    return (
      <div className="page">
        <BackButton onClick={onReturnBack} />
        <SectionHeader title={number.get('id') ? 'Update number' : 'Create number'} dividing />

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
              {formData.value.outbound_calls_enabled &&
              <div className="default-outbound-calls">
                <Field select="outbound_calls_default">
                  <Checkbox label="Make this number a default number for outgoing calls" />
                </Field>
                {formData.value.outbound_calls_default &&
                <Field select="outbound_calls_default_global">
                  <GlobalOutgoingNumber />
                </Field>}
                {formData.value.outbound_calls_default && !formData.value.outbound_calls_default_global &&
                <Field select="outbound_calls_default_countries">
                  <CountryCodeSelect multiple toggleAll={false} />
                </Field>}
              </div>}

              <button className={classNames('ui button', { loading: saving })}>
                {number.get('id') ? 'Update' : 'Create' }
              </button>
              <button
                className={classNames('ui basic button cancel-button', { disabled: saving })}
                onClick={this.onCancel}
              >
                Cancel
              </button>
              {number.get('id') &&
              <span className="voice-delete-button" onClick={this.onDelete}>
                Delete this number
              </span>}
            </Fieldset>
          </Form>
        </div>
      </div>
    );
  }
}

class GlobalOutgoingNumber extends Component {

  static propTypes = {
    value:    PropTypes.number,
    onChange: PropTypes.func
  };

  render() {
    const { value, onChange } = this.props;

    return (
      <div>
        <Radio
          label="Make this the default for all outgoing calls"
          choice={1}
          value={value}
          onChange={onChange}
        />
        <Radio
          choice={0}
          label="Make this the default for outgoing calls to specific countries"
          value={value}
          onChange={onChange}
        />
      </div>
    );
  }
}

export default NumberForm;
