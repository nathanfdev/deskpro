import React, { PropTypes } from 'react';
import { injectIntl, FormattedMessage } from 'react-intl';
import Isvg from 'react-inlinesvg';
import classNames from 'classnames';
import { Button } from 'DeskPRO/Component/Semantic/Button';
import sadFaceSvg from 'DeskPRO/Bundle/DemoBundle/Resources/img/sad_face.svg';

@injectIntl
class DeleteConfirm extends React.Component {
  static propTypes = {
    deleteConfirmOpen:    PropTypes.bool,
    onDeleteAccount:      PropTypes.func,
    onCloseDeleteConfirm: PropTypes.func
  };

  render() {
    return (
      <div className={classNames({ overlay: this.props.deleteConfirmOpen })} onClick={this.props.onCloseDeleteConfirm}>
        <div
          className={classNames('delete-confirm-popin positioned-element', { visible: this.props.deleteConfirmOpen })}
        >
          <div className="delete-confirm">
            <Isvg src={sadFaceSvg} />
            <h3>
              <FormattedMessage
                id="cloud.demo_expired.delete_account_title"
                defaultMessage="Delete your account"
              />
            </h3>
            <p>
              <FormattedMessage
                id="cloud.demo_expired.delete_account_desc"
                defaultMessage="Proceeding will erase all your data and
                you'll no longer be able to access your trial helpdesk"
              />
            </p>
            <Button
              onClick={this.props.onDeleteAccount}
              className="negative"
            >
              <FormattedMessage
                id="cloud.demo_expired.delete_account_button"
                defaultMessage="Delete account"
              />
            </Button>
            <Button
              onClick={this.props.onCloseDeleteConfirm}
              className="basic"
            >
              <FormattedMessage
                id="cloud.demo_expired.cancel"
                defaultMessage="Cancel"
              />
            </Button>
          </div>
        </div>
      </div>
    );
  }
}
export default DeleteConfirm;
