import PropTypes from 'prop-types';
import React from 'react';
import { connect } from 'react-redux';
import { FormattedMessage } from 'react-intl';
import Isvg from 'react-inlinesvg';
import { Segment } from 'DeskPRO/Component/Semantic/Segment';
import deleteSvg from 'DeskPRO/Bundle/DemoBundle/Resources/img/delete_reset.svg';
import preserveSvg from 'DeskPRO/Bundle/DemoBundle/Resources/img/preserve.svg';
import * as actions from '../Actions/extendActions';

@connect()
export class ConfirmExtendContainer extends React.Component {
  static propTypes = {
    dispatch: PropTypes.func.isRequired,
  };

  static contextTypes = {
    router: PropTypes.object.isRequired
  };

  constructor(props) {
    super(props);

    this.state = {
      submit: false,
    };
  }

  onDeleteData = () => {
    this.context.router.push('/confirm-reset');
  };

  onPreserveData = () => {
    const { dispatch } = this.props;
    this.setState({
      submit: true
    });

    dispatch(actions.preserveData())
      .then(() => { window.location.href = window.DESKPRO_BASE_URL; })
      .catch(window.location.href = '/');
  };

  render() {
    return (
      <ConfirmExtend
        onDeleteData={this.onDeleteData}
        onPreserveData={this.onPreserveData}
      />
    );
  }
}
export class ConfirmExtend extends React.Component {
  static propTypes = {
    onDeleteData:   PropTypes.func,
    onPreserveData: PropTypes.func
  };

  render() {
    return (
      <Segment className="confirm-extend">
        <h3>
          <FormattedMessage
            id="cloud.demo_expired.confirm_extend_title"
            defaultMessage="Great, your trial has been extended!"
          />
        </h3>
        <p>
          <FormattedMessage
            id="cloud.demo_expired.confirm_extend_desc"
            defaultMessage="Would you like to reset your trial and delete your previous data?
          Or, preserve your data from early in the trial and continue where your left off?"
          />
        </p>
        <button onClick={this.props.onDeleteData}>
          <Isvg src={deleteSvg} />
          <FormattedMessage
            id="cloud.demo_expired.confirm_extend_delete"
            defaultMessage="Delete data & reset trial"
          />
        </button>
        <div className="or">
          <div className="ui horizontal divider">
            <FormattedMessage
              id="cloud.demo_expired.or"
              defaultMessage="Or"
            />
          </div>
        </div>
        <button onClick={this.props.onPreserveData}>
          <Isvg src={preserveSvg} />
          <FormattedMessage
            id="cloud.demo_expired.confirm_extend_preserve"
            defaultMessage="Preserve data and continue"
          />
        </button>
      </Segment>
    );
  }
}
