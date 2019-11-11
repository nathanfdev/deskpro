import React from 'react';
import PropTypes from 'prop-types';
import toastr from 'toastr';
import MessengerSetup from '@deskpro/messenger-setup';
import Immutable from 'immutable';
import { connect } from 'react-redux';
import { Button } from '@deskpro/react-components';
import { getSettings, saveSettings } from '../Actions/messengerActions';
import { allChatDepartmentsSelector, allTicketDepartmentsSelector } from '../../Application/Selectors/departments';
import { loadChatDepartments, loadTicketDepartments } from '../../Application/Actions/departmentsActions';

@connect(state => ({
  chatDepartments:   allChatDepartmentsSelector(state),
  ticketDepartments: allTicketDepartmentsSelector(state),
}))
class MessengerSetupContainer extends React.Component {

  static propTypes = {
    dispatch:          PropTypes.func.isRequired,
    params:            PropTypes.object.isRequired,
    chatDepartments:   PropTypes.object,
    ticketDepartments: PropTypes.object,
  };

  state = {
    settings: Immutable.fromJS({})
  };

  componentDidMount() {
    const { dispatch } = this.props;

    dispatch(loadChatDepartments());
    dispatch(loadTicketDepartments());

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

  handleSubmit = () => {
    const { settings, saving } = this.state;
    const { dispatch, params: { brandId } } = this.props;

    if (saving) {
      return;
    }

    this.setState({
      saving: true
    });

    const promise = dispatch(saveSettings(brandId, settings));

    promise.success(() => {
      this.setState({
        saving: false
      }, () => {
        toastr.success('Settings saved!');
      });
    });
  };

  render() {
    const { settings, saving } = this.state;
    const {
      chatDepartments,
      ticketDepartments,
    } = this.props;
    return (
      <div>
        <MessengerSetup
          settings={settings}
          handleChange={this.onChange}
          chatDepartments={chatDepartments}
          ticketDepartments={ticketDepartments}
        />
        <Button loading={saving} onClick={this.handleSubmit} type="cta" size="large">Save</Button>
      </div>
    );
  }
}
export default MessengerSetupContainer;
