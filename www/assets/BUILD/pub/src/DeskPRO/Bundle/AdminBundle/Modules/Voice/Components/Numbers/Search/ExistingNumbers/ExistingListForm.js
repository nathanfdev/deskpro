import PropTypes from 'prop-types';
import React from 'react';
import { Fieldset, createValue } from '@deskpro/react-forms';
import { Form, Field, Select } from 'DeskPRO/Component/Semantic/ReactForm';
import AccountChoiceWrapper from '../../../Common/AccountChoiceWrapper';

class ExistingListForm extends React.Component {

  static propTypes = {
    value:    PropTypes.number,
    accounts: PropTypes.object,
    onChange: PropTypes.func
  };

  constructor(props) {
    super(props);
    this.state = {
      formData: createValue({
        value:    props.value,
        onChange: this.onChange
      })
    };
  }

  componentWillReceiveProps(nextProps) {
    this.setState({
      formData: createValue({
        value:    nextProps.value,
        onChange: this.onChange
      })
    });
  }

  onChange = (formData) => {
    this.setState({ formData });
    this.props.onChange(formData.value);
  };

  render() {
    const { accounts } = this.props;

    return (
      <div className="twilio-number-search-form">
        <Form formValue={this.state.formData}>
          <Fieldset>
            <Field select="account" label="Choose account *">
              <AccountChoiceWrapper accounts={accounts}>
                <Select clearable={false} />
              </AccountChoiceWrapper>
            </Field>
          </Fieldset>
        </Form>
      </div>
    );
  }
}

export default ExistingListForm;
