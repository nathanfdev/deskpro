import PropTypes from 'prop-types';
import React from 'react';
import { testCredentials } from '../../../Actions/accountActions';

class BaseAccountFormContainer extends React.Component {

  static propTypes = {
    dispatch: PropTypes.func,
    onClose:  PropTypes.func,
    children: PropTypes.node
  };

  constructor(props) {
    super(props);
    this.state = {
      errors:         {},
      saving:         false,
      testing:        false,
      deleting:       false,
      displaySuccess: false
    };
  }

  onSubmit = (data) => {
    this.setState({
      saving:         true,
      displaySuccess: false,
      errors:         {}
    });

    const promise = this.submitData(data);
    promise.success(() => {
      this.props.onClose();
    });
    promise.error((result) => {
      this.setState({
        errors: result.errors,
        saving: false
      });
    });
  };

  onTestCredentials = (data) => {
    this.setState({
      testing:        true,
      displaySuccess: false,
      errors:         {}
    });

    const promise = this.props.dispatch(testCredentials(data));
    promise.success(() => {
      this.setState({
        displaySuccess: true,
        testing:        false,
        errors:         {}
      });
    });
    promise.error((result) => {
      this.setState({
        errors:  result.errors,
        testing: false
      });
    });
  };

  render() {
    return React.cloneElement(this.props.children, {
      ...this.props,
      ...this.state,
      ...this.props.children.props,

      onSubmit:          this.onSubmit,
      onTestCredentials: this.onTestCredentials,
      onDeleteAccount:   this.onDeleteAccount
    });
  }
}

export default BaseAccountFormContainer;
