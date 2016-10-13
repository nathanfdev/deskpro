import React, { PropTypes } from 'react';
import { connect } from 'react-redux';
import $ from 'jquery';
import { editPerson } from '../../../../Application/Actions/peopleActions';

@connect()
class AudioWidgetContainer extends React.Component {

  static propTypes = {
    agent:    PropTypes.object,
    children: PropTypes.node,
    dispatch: PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      errors: {},
      saving: false
    };
  }

  onSubmit = (data) => {
    const { agent, dispatch } = this.props;
    this.setState({
      errors: {},
      saving: true
    });

    const agentData = agent.get('agent_data').toJS();
    const promise = dispatch(editPerson(agent.get('id'), {
      agent_data: {
        ...agentData,
        voicemail_asset: data
      }
    }));

    promise.success(() => {
      this.setState({
        saving: false
      }, () => this.widget.onClose());
    });
    promise.error((result) => {
      const agentDataErrors = result && result.errors && result.errors.fields && result.errors.fields && result.errors.fields.agent_data;
      const voicemailErrors = agentDataErrors && agentDataErrors.fields && agentDataErrors.fields.voicemail_asset;

      const errors = $.extend(true, voicemailErrors, {
        fields: {
          blob: {
            fields: {
              [data.type]: voicemailErrors
            }
          }
        }
      });

      this.setState({
        errors,
        saving: false
      });
    });
  };

  render() {
    const { agent, children } = this.props;

    return React.cloneElement(children, {
      ...children.props,
      ...this.state,

      ref:      (c) => { this.widget = c; },
      value:    agent.getIn(['agent_data', 'voicemail_asset']),
      onSubmit: this.onSubmit
    });
  }
}

export default AudioWidgetContainer;
