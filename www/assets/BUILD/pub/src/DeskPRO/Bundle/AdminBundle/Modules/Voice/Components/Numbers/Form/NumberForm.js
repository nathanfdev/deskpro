import PropTypes from 'prop-types';
import React from 'react';
import { Fieldset } from '@deskpro/react-forms';
import classNames from 'classnames';
import Modal from 'DeskPRO/Component/Semantic/Modal';
import { Button } from '@deskpro/react-components';
import { getPhoneCountryName, getPhoneCountryCode } from 'DeskPRO/Component/Util/PhoneNumber';
import { Input, Form, Field, Checkbox, Radio, CountryCodeSelect } from 'DeskPRO/Component/Semantic/ReactForm';
import BaseForm from 'DeskPRO/Component/Form/BaseForm';
import SectionHeader from '../../../../Common/Components/SectionHeader';
import BackButton from '../../../../Common/Components/BackButton';
import NumberTargetSelect from '../../Common/NumberTarget/NumberTargetSelect';

class NumberForm extends BaseForm {

  static propTypes = {
    number:        PropTypes.object,
    returnBack:    PropTypes.func,
    onSubmit:      PropTypes.func,
    disableNumber: PropTypes.func,
    deleteNumber:  PropTypes.func,
  };

  constructor(props) {
    super(props);
    this.state = {
      ...this.state,
      confirmDisabling: false,
      confirmDeletion:  false
    };
  }

  cancel = (event) => {
    event.preventDefault();
    this.props.onReturnBack();
  };

  showDisableConfirmation = (event) => {
    event.preventDefault();
    this.setState({
      confirmDisabling: true
    });
  };

  performDisable = (event) => {
    event.preventDefault();
    this.props.disableNumber();
    this.setState({
      confirmDisabling: false
    });
  };

  rejectDisable = (event) => {
    event.preventDefault();
    this.setState({
      confirmDisabling: false
    });
  };

  showDeleteConfirmation = (event) => {
    event.preventDefault();
    this.setState({
      confirmDeletion: true
    });
  };

  performDelete = (event) => {
    event.preventDefault();
    this.props.deleteNumber();
    this.setState({
      confirmDeletion: false
    });
  };

  rejectDelete = (event) => {
    event.preventDefault();
    this.setState({
      confirmDeletion: false
    });
  };

  getDefaultState() {
    const { number } = this.props;

    return {
      number:                           number.get('number'),
      nickname:                         number.get('nickname') || '',
      target:                           number.get('target') && number.get('target').toJS(),
      outbound_calls_enabled:           number.get('outbound_calls_enabled'),
      outbound_calls_default:           number.get('outbound_calls_default'),
      outbound_calls_default_type:      number.get('outbound_calls_default_type') || 'country',
      outbound_calls_default_countries: number.get('outbound_calls_default_countries') ? number.get('outbound_calls_default_countries').toJS() : [],
    };
  }

  transformSubmitData(submitData) { // eslint-disable-line
    delete submitData.number;
    return submitData;
  }

  render() {
    const { number, returnBack } = this.props;
    const { formData, saving, confirmDisabling, confirmDeletion } = this.state;
    const countryName = getPhoneCountryName(number.get('number'));
    const countryCode = getPhoneCountryCode(number.get('number'));

    return (
      <div className="page">
        <Modal
          isOpen={confirmDisabling}
          title="Are you sure you want to disable this number?"
          contentStyles={{ top: '25%', left: '37%', bottom: 'auto', height: '220px', width: '30%' }}
        >
          <h2>
            <p>
              {'A disabled number will be removed from queues and targets.'}
              {'The number won\'t be able to accept  or make calls.'}
            </p>
            <p>
              You will still retain ownership over the number and will continue
              to pay the rental fee. You can re-enable the number at any time
              in the future.
            </p>
          </h2>
          <div>
            <span style={{ float: 'left' }}>
              <Button size="large" type="secondary" onClick={this.rejectDisable}>Decline</Button>
            </span>
            <span style={{ float: 'right' }}>
              <Button size="large" type="cta" onClick={this.performDisable}>
                {'Yes, I\'m sure I want to disable this number'}
              </Button>
            </span>
          </div>
        </Modal>
        <Modal
          isOpen={confirmDeletion}
          title="Are you sure you want to delete this number?"
          contentStyles={{ top: '25%', left: '37%', bottom: 'auto', height: '180px', width: '30%' }}
        >
          <h2>
            Deleting the number will remove it from the helpdesk and
            release it from your control. You will no longer have to pay
            the rental fee for the number, but it will be made available
            for anyone else to purchase.
          </h2>
          <div>
            <span style={{ float: 'left' }}>
              <Button size="large" type="secondary" onClick={this.rejectDelete}>Decline</Button>
            </span>
            <span style={{ float: 'right' }}>
              <Button size="large" type="cta" onClick={this.performDelete}>
                {'Yes, I\'m sure I want to delete and release the number'}
              </Button>
            </span>
          </div>
        </Modal>
        <BackButton onClick={returnBack} />
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
                <div>
                  {countryName &&
                  <Field select="outbound_calls_default_type">
                    <Radio
                      choice="country"
                      label={`Make this the default for outgoing calls to ${countryName}`}
                    />
                  </Field>}
                  <Field select="outbound_calls_default_type">
                    <Radio
                      choice="specific"
                      label="Make this the default for outgoing calls to specific countries"
                    />
                  </Field>
                  {formData.value.outbound_calls_default_type === 'specific' &&
                  <Field select="outbound_calls_default_countries">
                    <CountryCodeSelect
                      multiple
                      uncheckAll
                      selectedCount
                      toggleAll={false}
                      primaryCountryCodes={[countryCode]}
                    />
                  </Field>}
                  <Field select="outbound_calls_default_type">
                    <Radio
                      label="Make this the default for all outgoing calls"
                      choice="all"
                    />
                  </Field>
                </div>}
              </div>}

              <button className={classNames('ui button', { loading: saving })}>
                {number.get('id') ? 'Update' : 'Create' }
              </button>
              <button
                className={classNames('ui basic button cancel-button', { disabled: saving })}
                onClick={this.cancel}
              >
                Cancel
              </button>
              {number.get('id') &&
              <span className="voice-delete-button" onClick={this.showDisableConfirmation}>
                Disable this number
              </span>}
              {number.get('id') &&
              <span className="voice-delete-button" onClick={this.showDeleteConfirmation}>
                Delete this number
              </span>}
            </Fieldset>
          </Form>
        </div>
      </div>
    );
  }
}

export default NumberForm;
