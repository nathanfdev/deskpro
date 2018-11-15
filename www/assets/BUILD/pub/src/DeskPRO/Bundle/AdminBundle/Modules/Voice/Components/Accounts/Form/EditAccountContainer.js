import { connect } from 'react-redux';
import { updateAccount, deleteAccount } from '../../../Actions/accountActions';
import BaseAccountFormContainer from './BaseAccountFormContainer';

@connect()
class EditAccountContainer extends BaseAccountFormContainer {

  onDeleteAccount = () => {
    const { dispatch, account, onClose } = this.props;

    this.setState({
      deleting:       true,
      displaySuccess: false,
      errors:         {}
    });

    const promise = dispatch(deleteAccount(account.get('id')));
    promise.success(() => {
      onClose();
    });
    promise.error((result) => {
      this.setState({
        errors:   result.errors,
        deleting: false
      });
    });
  };

  submitData = (data) => {
    const { dispatch, account } = this.props;

    return dispatch(updateAccount(account.get('type'), account.get('id'), data));
  };
}

export default EditAccountContainer;
