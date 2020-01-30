import React from 'react';
import PropTypes from 'prop-types';
import toastr from 'toastr';
import MessengerSetup from '@deskpro/messenger-setup';
import Immutable from 'immutable';
import { connect } from 'react-redux';
import { Button } from '@deskpro/react-components';
import { getSettings, saveSettings } from '../Actions/messengerActions';
import { allChatDepartmentsSelector, allTicketDepartmentsSelector } from '../../Application/Selectors/departments';
import { allChatCustomFields } from '../../Application/Selectors/chats';
import { allUserGroupsSelector } from '../../Application/Selectors/people';
import { loadChatDepartments, loadTicketDepartments } from '../../Application/Actions/departmentsActions';
import { loadChatCustomFieldsAction } from '../../Application/Actions/chatActions';
import { loadUserGroups } from '../../Application/Actions/peopleActions';

@connect(state => ({
  chatDepartments:   allChatDepartmentsSelector(state),
  ticketDepartments: allTicketDepartmentsSelector(state),
  chatCustomFields:  allChatCustomFields(state),
  usergroups:        allUserGroupsSelector(state),
}))
class MessengerSetupContainer extends React.Component {

  static propTypes = {
    dispatch:          PropTypes.func.isRequired,
    params:            PropTypes.object.isRequired,
    chatDepartments:   PropTypes.object,
    ticketDepartments: PropTypes.object,
    chatCustomFields:  PropTypes.object,
    usergroups:        PropTypes.object,
  };

  static defaultProps = {
    usergroups: new Immutable.Map()
  };

  state = {
    settings: Immutable.fromJS({})
  };

  componentDidMount() {
    const { dispatch } = this.props;

    dispatch(loadChatDepartments());
    dispatch(loadTicketDepartments());
    dispatch(loadChatCustomFieldsAction());
    dispatch(loadUserGroups());

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
      if (typeof value === 'object' && !Immutable.Iterable.isIterable(value)) {
        config = settings.mergeIn(keyPath, value);
      } else {
        config = settings.setIn(keyPath, value);
      }
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

    const postData = settings.setIn(
      ['chat', 'preChatForm', 'fields'],
      settings.getIn(['chat', 'preChatForm', 'fields']).filter(f => f && f.get('id'))
    );

    const promise = dispatch(saveSettings(brandId, postData));

    promise
      .success(() => {
        this.setState({
          saving: false
        }, () => {
          toastr.success('Settings saved!');
        });
      })
      .error(() => {
        this.setState({
          saving: false
        }, () => {
          toastr.error('Error when saving settings!');
        });
      })
    ;
  };

  render() {
    const { settings, saving } = this.state;
    const {
      chatDepartments,
      chatCustomFields,
      ticketDepartments,
      usergroups,
    } = this.props;
    return (
      <div>
        <MessengerSetup
          settings={settings}
          handleChange={this.onChange}
          chatDepartments={chatDepartments}
          chatCustomFields={chatCustomFields}
          ticketDepartments={ticketDepartments}
          usergroups={usergroups}
        >
          <Button loading={saving} onClick={this.handleSubmit} type="cta" size="large">Save</Button>
        </MessengerSetup>
      </div>
    );
  }
}
export default MessengerSetupContainer;
