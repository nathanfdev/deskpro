import React, { PropTypes } from 'react';
import { Fieldset, createValue } from 'react-forms';
import Immutable from 'immutable';
import { Form, Field, BlurInput } from 'DeskPRO/Component/Semantic/ReactForm';
import classNames from 'classnames';

class ExistingExtensionForm extends React.Component {

  static propTypes = {
    agent:    PropTypes.object,
    saving:   PropTypes.bool,
    deleting: PropTypes.bool,
    onSubmit: PropTypes.func,
    onDelete: PropTypes.func
  };

  constructor(props) {
    super(props);
    const agent = props.agent || Immutable.fromJS({});
    const agentData = (agent.get('agent_data') || Immutable.fromJS({
      extension_number: ''
    })).toJS();

    this.state = {
      formData: createValue({
        value: {
          agent_data: agentData
        },
        errorList: {},
        onChange:  this.onChange
      })
    };
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
    const { saving, deleting } = this.props;
    if (saving || deleting) {
      return;
    }

    this.props.onSubmit(formData.value);
  };

  render() {
    const { saving, deleting, onDelete } = this.props;
    const { formData } = this.state;

    return (
      <div>
        <Form onSubmit={(event) => { event.preventDefault(); }} formValue={formData}>
          <Fieldset>
            <Field select="agent_data">
              <Field select="extension_number" label="Extension">
                <BlurInput placeholder="e.g. '1001'" className={classNames({ disabled: saving || deleting })} />
              </Field>
            </Field>
          </Fieldset>
        </Form>
        <div className="delete-button">
          <button
            className={classNames('ui basic button', { loading: deleting, disabled: saving })}
            onClick={onDelete}
          >
            Delete account
          </button>
        </div>
      </div>
    );
  }
}

export default ExistingExtensionForm;
