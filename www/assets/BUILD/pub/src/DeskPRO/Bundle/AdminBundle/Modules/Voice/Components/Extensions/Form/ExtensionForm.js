import PropTypes from 'prop-types';
import React from 'react';
import { Fieldset } from 'react-forms';
import { Input, Form, Field } from 'DeskPRO/Component/Semantic/ReactForm';
import BaseForm from 'DeskPRO/Component/Form/BaseForm';
import classNames from 'classnames';
import SectionHeader from '../../../../Common/Components/SectionHeader';
import BackButton from '../../../../Common/Components/BackButton';
import AudioWidgetFormContainer from '../../Common/AudioWidgetFormContainer';

class ExtensionForm extends BaseForm {

  static propTypes = {
    agent:        PropTypes.object,
    deleting:     PropTypes.bool,
    onSubmit:     PropTypes.func,
    onDelete:     PropTypes.func,
    onReturnBack: PropTypes.func.isRequired
  };

  onCancel = (event) => {
    event.preventDefault();
    this.props.onReturnBack();
  };

  getDefaultState() {
    const { agent } = this.props;
    const voicemailAsset = agent && agent.getIn(['agent_data', 'voicemail_asset']);

    return {
      name:       agent.get('name'),
      agent_data: {
        extension_number: agent ? agent.getIn(['agent_data', 'extension_number']) : '',
        voicemail_asset:  voicemailAsset ? voicemailAsset.toJS() : null
      }
    };
  }

  render() {
    const { deleting, onDelete, onReturnBack } = this.props;
    const { formData, saving } = this.state;

    return (
      <div className="page">
        <BackButton onClick={onReturnBack} />
        <SectionHeader title="Update extension" dividing />

        <div className="twilio-queue-form">
          <Form onSubmit={this.onSubmit} formValue={formData}>
            <Fieldset>
              <Field select="name" label="Agent">
                <Input type="text" disabled="disabled" />
              </Field>
              <Fieldset select="agent_data">
                <div>
                  <Field select="extension_number" label="Extension">
                    <Input placeholder="e.g. '1001'" />
                  </Field>
                  <Field select="voicemail_asset" className="audio-asset" label="Voicemail">
                    <AudioWidgetFormContainer />
                  </Field>
                </div>
              </Fieldset>

              <br />
              <button className={classNames('ui button', { loading: saving })}>
                Update
              </button>
              <button
                className={classNames('ui basic button cancel-button', { disabled: saving })}
                onClick={this.onCancel}
              >
                Cancel
              </button>

              <span
                className={classNames('voice-delete-button', { disabled: saving || deleting })}
                onClick={onDelete}
              >
                Delete this extension
              </span>
            </Fieldset>
          </Form>
        </div>
      </div>
    );
  }
}

export default ExtensionForm;
