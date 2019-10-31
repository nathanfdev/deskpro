import PropTypes from 'prop-types';
import React from 'react';
import classNames from 'classnames';
import $ from 'jquery';
import 'intl-tel-input';
import Immutable from 'immutable';
import { Fieldset, createValue } from '@deskpro/react-forms';
import { Form, Field, Select } from 'DeskPRO/Component/Semantic/ReactForm';
import { getPhoneCountryName, getPhoneCountryCode } from 'DeskPRO/Component/Util/PhoneNumber';
import Modal from 'DeskPRO/Component/Semantic/Modal';
import { Button } from '@deskpro/react-components';
import SectionHeader from '../../../../Common/Components/SectionHeader';
import NumberTarget from '../../Common/NumberTarget/NumberTarget';
import VoiceTargetNameContainer from '../../Common/NumberTarget/VoiceTargetNameContainer';
import AccountChoiceWrapper from '../../Common/AccountChoiceWrapper';

const getCountryDialCode = (countryCode) => {
  const intlCountries = $.fn.intlTelInput.getCountryData();
  for (const i in intlCountries) {
    if (Object.hasOwnProperty.call(intlCountries, i)) {
      const intlCountry = intlCountries[i];
      if (intlCountry.iso2 === countryCode.toLowerCase()) {
        return ` (+${intlCountry.dialCode})`;
      }
    }
  }

  return '';
};

class NumberHeader extends React.Component {

  render() {
    return (
      <SectionHeader
        title="Numbers"
        description="All your phone numbers and which users are associated with them (individually or via teams)."
        dividing
      />
    );
  }
}

class NumberList extends React.Component {

  static propTypes = {
    accounts:             PropTypes.object,
    numbers:              PropTypes.object,
    queues:               PropTypes.object,
    disabledNumbers:      PropTypes.object,
    disabledFilter:       PropTypes.object,
    disabledLoading:      PropTypes.bool,
    goToAccounts:         PropTypes.func,
    goToAvailableNumbers: PropTypes.func,
    enableNumber:         PropTypes.func,
    editNumber:           PropTypes.func,
    releaseNumber:        PropTypes.func,
    changeDisabledFilter: PropTypes.func
  };

  render() {
    const { accounts, numbers, queues, disabledNumbers, disabledFilter, disabledLoading } = this.props;
    const { goToAccounts, goToAvailableNumbers, editNumber, enableNumber, releaseNumber, changeDisabledFilter } = this.props;

    if (!accounts.size) {
      return (
        <div className="page">
          <NumberHeader />

          You currently have no accounts.
          <br /><br />

          <button className="ui primary button" onClick={goToAccounts}>
            Open general settings
          </button>
        </div>
      );
    }

    return (
      <div className="page">
        <EnabledNumbersList
          numbers={numbers}
          queues={queues}
          goToAvailableNumbers={goToAvailableNumbers}
          editNumber={editNumber}
        />
        <br /><br />
        {disabledNumbers &&
          <DisabledNumbersList
            accounts={accounts}
            numbers={disabledNumbers}
            filter={disabledFilter}
            loading={disabledLoading}
            enableNumber={enableNumber}
            releaseNumber={releaseNumber}
            changeFilter={changeDisabledFilter}
          />}
      </div>
    );
  }
}

class EnabledNumbersList extends React.Component {

  static propTypes = {
    numbers:              PropTypes.object,
    queues:               PropTypes.object,
    goToAvailableNumbers: PropTypes.func,
    editNumber:           PropTypes.func
  };

  renderEmpty() {
    const { goToAvailableNumbers } = this.props;

    return (
      <div>
        <NumberHeader />

        You currently have no phone numbers.
        <br /><br />

        <button className="ui primary button" onClick={goToAvailableNumbers}>
          Add number
        </button>
      </div>
    );
  }

  renderTable() {
    const { numbers, queues } = this.props;
    const { goToAvailableNumbers, editNumber } = this.props;

    return (
      <div>
        <button className="ui right floated basic button" onClick={goToAvailableNumbers}>
          <i className="icon plus" />
          Add number
        </button>

        <NumberHeader />

        <div className="admin-list-table">
          <div className="row header">
            <div className="column location">Loc.</div>
            <div className="column number">Number</div>
            <div className="column nickname">Nickname</div>
            <div className="column targets">Target</div>
          </div>
          {numbers.sortBy(number => -number.get('id')).toArray().map(number =>
            <EnabledNumberRow
              key={number}
              number={number}
              queues={queues}
              editNumber={editNumber}
            />
          )}
        </div>
      </div>
    );
  }

  render() {
    const { numbers } = this.props;
    if (numbers && numbers.size) {
      return this.renderTable();
    }

    return this.renderEmpty();
  }
}

class EnabledNumberRow extends React.Component {

  static propTypes = {
    number:     PropTypes.object,
    editNumber: PropTypes.func
  };

  onToggleOptions = (event) => {
    event.preventDefault();

    const { number, editNumber } = this.props;
    editNumber(number);
  };

  render() {
    const { number } = this.props;
    const countryName = getPhoneCountryName(number.get('number'));
    const countryCodes = number.get('outbound_calls_default_countries')
      .toArray()
      .map(countryCode => countryCode.toUpperCase() + getCountryDialCode(countryCode))
      .join(', ');

    return (
      <div className="row" key={number.get('id')}>
        <div className="info">
          <div className="column location">
            <div className={classNames('flag-icon', `flag-icon-${getPhoneCountryCode(number.get('number')).toLowerCase()}`)} />
          </div>
          <div className="column number">{number.get('number')}</div>
          <div className="column nickname">
            {number.get('nickname') ? number.get('nickname') : number.get('number')}
          </div>
          <div className="column targets">
            {number.get('target') &&
            <VoiceTargetNameContainer target={number.get('target')}>
              <NumberTarget withType />
            </VoiceTargetNameContainer>
            }
          </div>
          <div className="column press-options">
            {number.get('outbound_calls_enabled') &&
            <span className="press-option">
              Allow outbound calls from this number <i className="icon checkmark" />
            </span>}
          </div>
          <div className="column press-options">
            {number.get('outbound_calls_default') &&
            <span className="press-option">
              {number.get('outbound_calls_default_type') === 'all' ? 'Default phone number for all outbound calls' : ''}
              {number.get('outbound_calls_default_type') === 'country' ? `Make this the default for outgoing calls to ${countryName}` : ''}
              {number.get('outbound_calls_default_type') === 'specific' && countryCodes
                ? `Default phone number for outbound calls to: ${countryCodes}` : ''}
            </span>}
          </div>
          <div className="column options-button">
            <a onClick={this.onToggleOptions}>
              <i className="fas fa-cog" />
            </a>
          </div>
          <div style={{ clear: 'both' }} />
        </div>
      </div>
    );
  }
}

class DisabledNumbersList extends React.Component {

  static propTypes = {
    loading:       PropTypes.bool,
    accounts:      PropTypes.object,
    filter:        PropTypes.object,
    numbers:       PropTypes.object,
    changeFilter:  PropTypes.func,
    enableNumber:  PropTypes.func,
    releaseNumber: PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      numberToDelete:  null,
      confirmDeletion: false
    };
  }

  showDeleteConfirmation = (number) => {
    this.setState({
      numberToDelete:  number,
      confirmDeletion: true
    });
  };

  rejectDelete = () => {
    this.setState({
      numberToDelete:  null,
      confirmDeletion: false
    });
  };

  performDelete = () => {
    this.props.releaseNumber(this.state.numberToDelete);
    this.setState({
      numberToDelete:  null,
      confirmDeletion: false
    });
  };

  render() {
    const { accounts = Immutable.fromJS([]), numbers = Immutable.fromJS([]) } = this.props;
    const { filter, loading } = this.props;
    const { enableNumber, changeFilter } = this.props;
    const { confirmDeletion } = this.state;

    return (
      <div>
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
        <SectionHeader title="Disabled Numbers" />
        {accounts.size > 1 &&
        <DisabledListForm
          value={filter}
          accounts={accounts}
          onChange={changeFilter}
        />}
        {loading && <div className="ui active centered inline loader" />}
        <div>
          <div className="admin-list-table">
            {numbers.filter(number => !number.get('added')).map(number =>
              <DisabledNumberRow
                number={number}
                enableNumber={enableNumber}
                releaseNumber={this.showDeleteConfirmation}
              />
            )}
          </div>
        </div>
      </div>
    );
  }
}

class DisabledNumberRow extends React.Component {

  static propTypes = {
    number:        PropTypes.object,
    enableNumber:  PropTypes.func,
    releaseNumber: PropTypes.func,
  };

  enableNumber = (event) => {
    const { number, enableNumber } = this.props;
    event.preventDefault();

    enableNumber(number);
  };

  releaseNumber = (event) => {
    const { number, releaseNumber } = this.props;
    event.preventDefault();

    releaseNumber(number);
  };

  render() {
    const { number } = this.props;
    const countryCode = number.get('number_country_code');
    const nationalNumber = number.get('national_number');

    return (
      <div className="row" key={number.get('id')}>
        <div className="info">
          <div className="column location">
            <div className={classNames('flag-icon', `flag-icon-${getPhoneCountryCode(`+${countryCode}${nationalNumber}`).toLowerCase()}`)} />
          </div>
          <div className="column number">+{countryCode} {nationalNumber}</div>
          <div className="column disabled-number-buttons">
            <a onClick={this.releaseNumber}>Delete Number</a>
            <button className="ui button" onClick={this.enableNumber}>Enable Number</button>
          </div>
          <div style={{ clear: 'both' }} />
        </div>
      </div>
    );
  }
}

class DisabledListForm extends React.Component {

  static propTypes = {
    value:    PropTypes.number,
    accounts: PropTypes.object,
    onChange: PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      formData: createValue({
        value:    props.value,
        onChange: this.onChange
      })
    };
  }

  componentWillReceiveProps(nextProps) {
    this.setState({
      formData: createValue({
        value:    nextProps.value,
        onChange: this.onChange
      })
    });
  }

  onChange = (formData) => {
    this.setState({ formData });
    this.props.onChange(formData.value);
  };

  render() {
    const { accounts } = this.props;

    return (
      <div className="twilio-number-search-form">
        <Form formValue={this.state.formData}>
          <Fieldset>
            <Field select="account" label="Choose account *">
              <AccountChoiceWrapper accounts={accounts}>
                <Select clearable={false} />
              </AccountChoiceWrapper>
            </Field>
          </Fieldset>
        </Form>
      </div>
    );
  }
}

export default NumberList;
