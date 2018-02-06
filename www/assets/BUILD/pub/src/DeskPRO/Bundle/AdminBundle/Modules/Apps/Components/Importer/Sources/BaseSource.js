import React, { PropTypes } from 'react';
import classNames from 'classnames';
import { Fieldset, createValue } from '@deskpro/react-forms';
import { Form } from 'DeskPRO/Component/Semantic/ReactForm';
import BaseForm from 'DeskPRO/Component/Form/BaseForm';
import SectionHeader from '../../../../Common/Components/SectionHeader';
import BackButton from '../../../../Common/Components/BackButton';

class BaseSource extends BaseForm {

  static propTypes = {
    type:         PropTypes.string,
    source:       PropTypes.object,
    onTest:       PropTypes.func,
    onReturnBack: PropTypes.func
  };

  onTest = (event) => {
    event.preventDefault();

    const { onTest } = this.props;
    const { formData } = this.state;

    this.setState({
      testSuccess: false,
      testing:     true
    });

    const data = this.transformSubmitData(formData.value);
    const promise = onTest(data);
    promise.success(() => {
      if (this.mounted) {
        this.setState({
          testSuccess: true,
          testing:     false
        });
      }
    });
    promise.error(({ message, errors }) => {
      let errorList = errors;
      if (message && !errors) {
        errorList = {
          errors: [
            { message }
          ]
        };
      }

      this.setState({
        formData: createValue({
          value:    this.state.formData.value,
          onChange: this.onChange,
          errorList
        }),
        testing: false
      });
    });
  };

  transformSubmitData(data) {
    return { ...data, type: this.props.type };
  }

  render() {
    const { onReturnBack, source } = this.props;
    const { formData, saving, testing, testSuccess } = this.state;

    return (
      <div className="page admin-importer">
        <BackButton onClick={onReturnBack} />
        <SectionHeader
          title={`Data Importer: ${source.title}`}
          description={source.description}
          dividing
        />

        <Form className="ui form admin-importer-form" onSubmit={this.onSubmit} formValue={formData}>
          <Fieldset>
            <Fieldset select="options">
              {this.getFormFields()}
            </Fieldset>
          </Fieldset>

          <br />
          <button className={classNames('ui button', { loading: testing, disabled: saving })} onClick={this.onTest}>
            Test
          </button>
          <button className={classNames('ui button', { loading: saving, disabled: testing })}>
            Next
          </button>
        </Form>

        {testSuccess &&
        <div className="ui positive message">
          Your settings are correct
        </div>}
      </div>
    );
  }
}

export default BaseSource;
