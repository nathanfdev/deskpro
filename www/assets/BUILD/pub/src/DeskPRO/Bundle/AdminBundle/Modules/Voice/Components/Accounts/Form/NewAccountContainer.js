import { connect } from 'react-redux';
import { createAccount } from '../../../Actions/accountActions';
import BaseAccountFormContainer from './BaseAccountFormContainer';

@connect()
class NewAccountContainer extends BaseAccountFormContainer {

  submitData = (data) => {
    const { accountType, dispatch } = this.props;
    return dispatch(createAccount(accountType, data));
  }
}

export default NewAccountContainer;
