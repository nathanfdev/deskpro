import React, { PropTypes } from 'react';
import { Fieldset, Input, createValue } from 'react-forms';
import Immutable from 'immutable';
import { Form, Field } from 'DeskPRO/Component/Semantic/ReactForm';
import classNames from 'classnames';
import SectionHeader from '../../../../Common/Components/SectionHeader';
import BackButton from '../../../../Common/Components/BackButton';

class ExtensionForm extends React.Component {

  static propTypes = {
    agent:        PropTypes.object,
    saving:       PropTypes.bool,
    deleting:     PropTypes.bool,
    onSubmit:     PropTypes.func,
    onDelete:     PropTypes.func,
    onReturnBack: PropTypes.func.isRequired
  };

  constructor(props) {
    super(props);
    this.state = this.getDefaultState();
  }

  componentWillReceiveProps(nextProps) {
    this.setState({
      formData: createValue({
        value:     this.state.formData.value,
        errorList: nextProps.errors,
        onChange:  this.onChange
      })
    });
  }

  onChange = (formData) => {
    this.setState({ formData });
  };

  onSubmit = (event) => {
    event.preventDefault();

    const { onSubmit, saving, deleting } = this.props;
    const { formData } = this.state;

    if (saving || deleting) {
      return;
    }

    onSubmit(formData.value);
  };

  onCancel = (event) => {
    event.preventDefault();
    this.props.onReturnBack();
  };

  getDefaultState() {
    const { agent } = this.props;
    const agentData = (agent.get('agent_data') || Immutable.fromJS({
      extension_number: ''
    })).toJS();

    return {
      formData: createValue({
        value: {
          name:       agent.get('name'),
          agent_data: agentData
        },
        errorList: {},
        onChange:  this.onChange
      })
    };
  }

  render() {
    const { saving, deleting, onDelete, onReturnBack } = this.props;
    const { formData } = this.state;

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
              <Field select="agent_data">
                <Field select="extension_number" label="Extension">
                  <Input placeholder="e.g. '1001'" />
                </Field>
              </Field>

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
