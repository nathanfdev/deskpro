import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import * as card from 'DeskPRO/Component/Form/Card';
import { hasErrors } from 'DeskPRO/Component/Form/FormErrors';
import * as actions from '../Actions/extendActions';
import ExtendTrial from './ExtendTrial';

@connect()
class ExtendTrialContainer extends React.Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
  };

  static contextTypes = {
    router: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);

    this.state = {
      address:      '',
      city:         '',
      postCode:     '',
      state:        '',
      country:      '',
      cardName:     '',
      cardNumber:   '',
      expiryMonth:  '',
      expiryYear:   '',
      securityCode: '',
      submit:       false,
      errors:       null,
      countries:    {},
      states:       {}
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
  }

  componentDidMount() {
    this.mounted = true;
  }

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
    if (card.validateYear(value)) {
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

  onDeleteAccount = () => {
    this.context.router.push('/delete-account');
  };

  onResumeTrial = () => {
    const { dispatch } = this.props;
    this.setState({
      submit: true
    });

    const promise = dispatch(actions.extendTrial({
      address:      this.state.address,
      city:         this.state.city,
      postCode:     this.state.postCode,
      state:        this.state.state,
      country:      this.state.country,
      cardName:     this.state.cardName,
      cardNumber:   this.state.cardNumber,
      expiryMonth:  this.state.expiryMonth,
      expiryYear:   this.state.expiryYear,
      securityCode: this.state.securityCode
    }));

    promise.then(
      (response) => {
        if (this.mounted) {
          this.setState({
            submit: false,
            errors: null
          });
          if (response.getData() === 'OK') {
            this.context.router.push('/confirm-extend');
          }
        }
      },
      (response) => {
        if (this.mounted) {
          this.setState({
            submit: false,
            errors: response.getData().errors
          });
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
        onChangeCardName={this.onChangeCardName}
        onChangeCardNumber={this.onChangeCardNumber}
        onChangeExpiryMonth={this.onChangeExpiryMonth}
        onChangeExpiryYear={this.onChangeExpiryYear}
        onChangeSecurityCode={this.onChangeSecurityCode}
        onResumeTrial={this.onResumeTrial}
        onDeleteAccount={this.onDeleteAccount}
        {...this.state}
      />
    );
  }
}
export default ExtendTrialContainer;
