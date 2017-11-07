import objGet from 'lodash/get';
import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import * as card from 'DeskPRO/Component/Form/Card';
import { hasErrors } from 'DeskPRO/Component/Form/FormErrors';
import { meSelector } from 'DeskPRO/Bundle/AppBundle/Modules/RecordsStore/Shortcuts/me';
import geoIpSelector from '../../Application/Selectors/demoSelectors';
import * as actions from '../Actions/extendActions';
import ExtendTrial from './ExtendTrial';

@connect(state => ({
  me:    meSelector(state),
  geoIp: geoIpSelector(state)
}))
class ExtendTrialContainer extends React.Component {
  static propTypes = {
    me:       PropTypes.object,
    geoIp:    PropTypes.object,
    dispatch: PropTypes.func.isRequired
  };

  static contextTypes = {
    router: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);

    this.state = {
      address:           '',
      city:              '',
      postCode:          '',
      state:             '',
      country:           '',
      vatId:             '',
      schedule:          'monthly',
      cardName:          '',
      cardNumber:        '',
      expiryMonth:       '',
      expiryYear:        '',
      securityCode:      '',
      submit:            false,
      errors:            null,
      countries:         {},
      euCountries:       [],
      states:            {},
      question:          '',
      submitQuestion:    false,
      deleteConfirmOpen: false,
      questionOpened:    false,
      questionSent:      false
    };
  }

  componentWillMount() {
    const { dispatch } = this.props;
    const countryPromise = dispatch(actions.getCountries());

    countryPromise.then(
      (response) => {
        this.setState({
          countries: response.getData()
        });
      }
    );

    const statePromise = dispatch(actions.getStates());

    statePromise.then(
      (response) => {
        this.setState({
          states: response.getData()
        });
      }
    );

    const euCountriesPromise = dispatch(actions.getEuCountries());

    euCountriesPromise.then(
      (response) => {
        this.setState({
          euCountries: response.getData()
        });
      }
    );

    if (this.props.me) {
      this.setState({
        cardName: this.props.me.get('name')
      });
    }

    if (this.props.geoIp) {
      this.setState({
        country: this.props.geoIp.get('country_code')
      });
    }
  }

  componentDidMount() {
    this.mounted = true;
  }

  componentWillReceiveProps = (newProps) => {
    if (newProps.me) {
      this.setState({
        cardName: newProps.me.get('name')
      });
    }
    if (newProps.geoIp) {
      this.setState({
        country: newProps.geoIp.get('country_code')
      });
    }
  };

  componentWillUnmount() {
    this.mounted = false;
  }

  onChangeAddress = (value) => {
    this.setState({
      address: value
    });
  };

  onChangeCity = (value) => {
    this.setState({
      city: value
    });
  };

  onChangePostCode = (value) => {
    this.setState({
      postCode: value
    });
  };

  onChangeState = (value) => {
    this.setState({
      state: value
    });
  };

  onChangeCountry = (value) => {
    if (this.state.country === 'US' || value === 'US') {
      this.setState({
        state: ''
      });
    }
    this.setState({
      country: value
    });
  };

  onChangeVatId = (value) => {
    this.setState({
      vatId: value
    });
  };

  onChangeCardName = (value) => {
    this.setState({
      cardName: value
    });
  };

  onChangeCardNumber = (value) => {
    let errors = this.state.errors;
    if (card.validateCard(value)) {
      if (hasErrors(errors, 'card_number')) {
        delete errors.fields.card_number;
      }
    } else {
      errors = Object.assign(errors || {}, { fields: { card_number: { errors: ['Invalid'] } } });
    }
    this.setState({
      cardNumber: value,
      errors
    });
  };

  onChangeExpiryMonth = (value) => {
    if (value.length > 2) {
      return;
    }
    let errors = this.state.errors;
    if (card.validateMonth(value)) {
      if (hasErrors(errors, 'card_expiry')) {
        delete errors.fields.card_expiry;
      }
    } else {
      errors = Object.assign(errors || {}, { fields: { card_expiry: { errors: ['Invalid'] } } });
    }
    this.setState({
      expiryMonth: value,
      errors
    });
  };

  onChangeExpiryYear = (value) => {
    if (value.length > 4) {
      return;
    }
    let errors = this.state.errors;
    if (this.state.expiryMonth) {
      if (card.validateExpiry(`${this.state.expiryMonth}/${value}`)) {
        if (hasErrors(errors, 'card_expiry')) {
          delete errors.fields.card_expiry;
        }
      } else {
        errors = Object.assign(errors || {}, { fields: { card_expiry: { errors: ['Invalid'] } } });
      }
    } else if (card.validateYear(value)) {
      if (hasErrors(errors, 'card_expiry')) {
        delete errors.fields.card_expiry;
      }
    } else {
      errors = Object.assign(errors || {}, { fields: { card_expiry: { errors: ['Invalid'] } } });
    }
    this.setState({
      expiryYear: value,
      errors
    });
  };

  onChangeSecurityCode = (value) => {
    if (value.length <= card.ccvLength(this.state.cardNumber)) {
      this.setState({
        securityCode: value
      });
    }
  };

  onOpenDeleteConfirm = () => {
    this.setState({
      deleteConfirmOpen: true
    });
  };

  onCloseDeleteConfirm = () => {
    this.setState({
      deleteConfirmOpen: false
    });
  };

  onDeleteAccount = () => {
    this.context.router.push('/delete-account');
  };

  onChangeQuestion = (value) => {
    this.setState({
      question: value
    });
  };

  onOpenQuestion = () => {
    this.setState({
      questionOpened: true
    });
  };

  onCloseQuestion = () => {
    this.setState({
      questionOpened: false
    });
  };

  onSubmitQuestion = () => {
    const { dispatch } = this.props;
    this.setState({
      submitQuestion: true
    });

    const promise = dispatch(actions.submitQuestion({
      question: this.state.question
    }));

    promise.then(
      () => {
        this.setState({
          submitQuestion: false,
          questionOpened: false,
          questionSent:   true
        });
      },
      (response) => {
        if (this.mounted) {
          this.setState({
            submitQuestion: false,
            errors:         response.getData().errors
          });
        }
      }
    );
  };

  onResumeTrial = () => {
    const { dispatch } = this.props;
    this.setState({
      submit: true
    });

    let cardType;
    const cardNumber = this.state.cardNumber;

    if (card.isCardAmex(cardNumber)) {
      cardType = 'AMEX';
    } else if (card.isCardVisa(cardNumber)) {
      cardType = 'VISA';
    } else if (card.isCardMasterCard(cardNumber)) {
      cardType = 'MC';
    } else {
      cardType = '??';
    }

    const promise = dispatch(actions.extendTrial({
      address:      this.state.address,
      city:         this.state.city,
      postCode:     this.state.postCode,
      state:        this.state.state,
      country:      this.state.country,
      vatId:        this.state.vatId,
      schedule:     'monthly',
      cardName:     this.state.cardName,
      cardNumber,
      cardType,
      expiryMonth:  this.state.expiryMonth,
      expiryYear:   this.state.expiryYear,
      securityCode: this.state.securityCode
    }));

    const translateErrorCode = (code) => {
      let parts;
      switch (code) {
        case 'card.name':   return ['card_name',     { code: 'required', message: 'This field is required' }];
        case 'card.number': return ['card_number',   { code: 'invalid', message: 'Please enter a valid number' }];
        case 'card.exp_mm': return ['card_expiry',   { code: 'required', message: 'This field is required' }];
        case 'card.exp_yy': return ['card_expiry',   { code: 'required', message: 'This field is required' }];
        case 'card.sec':    return ['security_code', { code: 'required', message: 'This field is required' }];

        case 'user.address':    return ['address',  { code: 'required', message: 'This field is required' }];
        case 'user.city':       return ['city',     { code: 'required', message: 'This field is required' }];
        case 'user.post_code':  return ['postcode', { code: 'required', message: 'This field is required' }];
        case 'user.country':    return ['country',  { code: 'required', message: 'This field is required' }];
        case 'user.state':      return ['state',    { code: 'required', message: 'This field is required' }];
        case 'user.vat_id':     return ['vat_id',   { code: 'invalid', message: 'Please enter a valid VAT ID' }];

        case 'processor_error': return [
          'processor_error',
          {
            code:    'error',
            message: 'There was a problem trying to process your payment, please try again'
          }
        ];

        default:
          parts = code.split('.');
          parts.pop();
          if (parts.length) {
            return translateErrorCode(parts.join('.'));
          }

          return null;
      }
    };

    const errorCodeMapper = (errors) => {
      const errorMap = {};
      errors.forEach((code) => {
        const info = translateErrorCode(code);
        if (info) {
          errorMap[info[0]] = {
            errors: [info[1]]
          };
        }
      });

      return {
        fields: errorMap
      };
    };

    const handleResponse = (response) => {
      const data = response.getData();
      if (objGet(data, 'data.success') === true) {
        this.context.router.push('/confirm-extend');
      } else if (objGet(data, 'data.error_info.form_errors')) {
        this.setState({
          submit: false,
          errors: errorCodeMapper(objGet(data, 'data.error_info.form_errors'))
        });
      } else {
        this.setState({
          submit: false,
          errors: errorCodeMapper(['processor_error'])
        });
      }
    };

    promise.then(
      (response) => {
        if (this.mounted) {
          this.setState({
            submit: false
          });
          handleResponse(response);
        }
      },
      (response) => {
        if (this.mounted) {
          this.setState({
            submit: false
          });
          if (response) {
            handleResponse(response);
          } else {
            this.setState({
              submit: false,
              errors: errorCodeMapper(['processor_error'])
            });
          }
        }
      }
    );
  };

  render() {
    return (
      <ExtendTrial
        onChangeAddress={this.onChangeAddress}
        onChangeCity={this.onChangeCity}
        onChangePostCode={this.onChangePostCode}
        onChangeState={this.onChangeState}
        onChangeCountry={this.onChangeCountry}
        onChangeVatId={this.onChangeVatId}
        onChangeCardName={this.onChangeCardName}
        onChangeCardNumber={this.onChangeCardNumber}
        onChangeExpiryMonth={this.onChangeExpiryMonth}
        onChangeExpiryYear={this.onChangeExpiryYear}
        onChangeSecurityCode={this.onChangeSecurityCode}
        onResumeTrial={this.onResumeTrial}
        onOpenDeleteConfirm={this.onOpenDeleteConfirm}
        onCloseDeleteConfirm={this.onCloseDeleteConfirm}
        onDeleteAccount={this.onDeleteAccount}
        onSubmitQuestion={this.onSubmitQuestion}
        onOpenQuestion={this.onOpenQuestion}
        onCloseQuestion={this.onCloseQuestion}
        onChangeQuestion={this.onChangeQuestion}
        {...this.state}
      />
    );
  }
}
export default ExtendTrialContainer;
