import PropTypes from 'prop-types';
import React from 'react';
import jQuery from 'jquery';
import { NotAgentWarning } from './NotAgentWarning';
import { TooManyAttempts } from './TooManyAttempts';
import { WrongHelpdesk } from './WrongHelpdesk';
import { TimeLocked } from './TimeLocked';

export class WarningMajorWrapper extends React.Component {

  static propTypes = {
    type: PropTypes.string
  };

  componentDidMount() {
    const $container = jQuery('.dpw-login-warning-major');
    if ($container && $container.css('height')) {
      const height = (parseInt($container.css('height').replace(/px/, ''), 10) * -1) + 'px';
      $container.css('top', height);
    }
  }

  getContent(type) {
    switch (type) {
      case 'notAgent':
        return (<NotAgentWarning />);
      case 'tooManyAttempts':
        return (<TooManyAttempts />);
      case 'wrongHelpdesk':
        return (<WrongHelpdesk />);
      case 'timeLocked':
        return (<TimeLocked />);
      default:
        return null;
    }
  }

  render() {
    const content = this.getContent(this.props.type);
    if (content) {
      return (
        <div className="dpw-login-warning-major">
          {content}
        </div>
      );
    }

    return null;
  }
}
