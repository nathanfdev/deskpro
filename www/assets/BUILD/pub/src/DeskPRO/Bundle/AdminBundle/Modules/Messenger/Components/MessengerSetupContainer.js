import React from 'react';
import PropTypes from 'prop-types';
import MessengerSetup from '@deskpro/messenger-setup';
import Immutable from 'immutable';
import { connect } from 'react-redux';
import { Button } from '@deskpro/react-components';
import { getSettings, saveSettings } from '../Actions/messengerActions';

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
      response.data.data.chat.ticketsDefault = { department: response.data.data.chat.department };
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

  handleSubmit = () => {
    this.props.dispatch(saveSettings(this.props.params.brandId, this.state.settings));
  };

  render() {
    const { settings } = this.state;
    return (
      <div>
        <MessengerSetup
          settings={settings}
          handleChange={this.onChange}
        />
        <Button onClick={this.handleSubmit} type="cta" size="large">Save</Button>
      </div>
    );
  }
}
export default MessengerSetupContainer;
