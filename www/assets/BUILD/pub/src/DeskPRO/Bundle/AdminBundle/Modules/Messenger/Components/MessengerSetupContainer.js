import React from 'react';
import PropTypes from 'prop-types';
import MessengerSetup from '@deskpro/messenger-setup';
import Immutable from 'immutable';
import { connect } from 'react-redux';
import { getSettings } from '../Actions/messengerActions';

@connect()
class MessengerSetupContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func.isRequired,
    params:   PropTypes.object.isRequired,
  };

  state = {
    settings: Immutable.fromJS({})
  };

  componentDidMount() {
    this.props.dispatch(getSettings(this.props.params.brandId)).then((response) => {
      const newSettings = this.state.settings.merge(response.data.data);
      this.setState({ settings: newSettings });
    });
  }

  onChange = (value, name) => {
    const { settings } = this.state;

    let config;
    if (typeof value === 'function') {
      config = settings.withMutations(value);
    } else if (name) {
      const keyPath = name.split('.');
      config = settings.setIn(keyPath, value);
    }
    if (config) {
      this.setState({ settings: config });
    }
  };

  render() {
    const { settings } = this.state;
    return (
      <MessengerSetup
        settings={settings}
        handleChange={this.onChange}
      />
    );
  }
}
export default MessengerSetupContainer;
