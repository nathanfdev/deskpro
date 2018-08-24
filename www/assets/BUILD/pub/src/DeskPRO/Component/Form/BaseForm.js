import PropTypes from 'prop-types';
import React from 'react';
import { createValue } from '@deskpro/react-forms';

class BaseForm extends React.Component {

  static propTypes = {
    onSubmit:   PropTypes.func,
    autoSubmit: PropTypes.bool
  };

  constructor(props) {
    super(props);
    this.state = {
      formData: createValue({
        value:     this.getDefaultState ? this.getDefaultState() : {},
        errorList: {},
        onChange:  this.onChange
      }),
      saving: false
    };
  }

  componentDidMount() {
    this.mounted = true;
  }

  componentWillUnmount() {
    this.mounted = false;
  }

  onChange = (formData) => {
    const { autoSubmit } = this.props;

    this.setState({ formData }, () => {
      if (autoSubmit) {
        this.onSubmit();
      }
    });
  };

  onSubmit = (event) => {
    if (event) {
      event.preventDefault();
    }

    const { onSubmit } = this.props;
    const { formData, saving } = this.state;

    if (saving) {
      return;
    }

    this.setState({
      saving: true
    });

    let submitData = formData.value;
    if (this.transformSubmitData) {
      submitData = this.transformSubmitData({ ...formData.value });
    }

    const promise = onSubmit(submitData);
    promise.success(() => {
      if (this.mounted) {
        this.setState({
          saving: false
        });
      }
    });
    promise.error(({ message, errors }) => {
      if (this.mounted) {
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
            errorList,
            onChange: this.onChange
          }),
          saving: false
        });
      }
    });
  };
}

export default BaseForm;
