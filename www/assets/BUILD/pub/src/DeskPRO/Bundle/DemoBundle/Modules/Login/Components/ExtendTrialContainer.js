import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
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
      email:     '',
      country:   '',
      state:     '',
      submit:    false,
      errors:    null,
      countries: {},
      states:    {}
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

  onChangeAddress = (value) => {
    this.setState({
      address: value
    });
  };

  onChangeCardNumber = (value) => {
    this.setState({
      cardNumber: value
    });
  };

  onSelectCountry = (value) => {
    if (this.state.country === 'US' || value === 'US') {
      this.setState({
        state: ''
      });
    }
    this.setState({
      country: value
    });
  };

  onChangeState = (value) => {
    this.setState({
      state: value
    });
  };

  onDeleteAccount = () => {
    this.context.router.push('/delete-account');
  };

  onResumeTrial = () => {
    // Do API call and then
    this.context.router.push('/confirm-extend');
  };

  render() {
    return (
      <ExtendTrial
        onChangeAddress={this.onChangeAddress}
        onChangeCardNumber={this.onChangeCardNumber}
        onSelectCountry={this.onSelectCountry}
        onChangeState={this.onChangeState}
        onResumeTrial={this.onResumeTrial}
        onDeleteAccount={this.onDeleteAccount}
        {...this.state}
      />
    );
  }
}
export default ExtendTrialContainer;
